<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ElevatorResource;
use App\Jobs\MoveElevator;
use App\Models\Elevator;
use App\Models\ElevatorLog;
use App\Models\PendingElevatorCall;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ElevatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $elevators = Elevator::with('logs')->get();

        return $this->success(
            'Elevators retrieved successfully',
            ElevatorResource::collection($elevators)
        );
    }

    public function callElevator(Elevator $elevator, Request $request)
    {
        $building = $elevator->building;

        $request->validate([
            'target_floor' => [
                'required',
                'integer',
                Rule::between(1, $building->number_of_floors)
                    ->message("The target floor must be between 1 and {$building->number_of_floors}.")
            ]
        ]);

        $targetFloor = $request->input('target_floor');
        $currentFloor = $elevator->logs->latest()->value('current_floor');
        $direction = ($targetFloor > $currentFloor) ? 'up' : 'down';

        // Check if elevator is not in idle state
        if ($elevator->state !== 'idle') {
            // Store the elevator call as a pending call
            $pendingCall = PendingElevatorCall::create([
                'elevator_id' => $elevator->id,
                'target_floor' => $targetFloor,
                'executed' => false, // Initialize as not executed
            ]);

            // Return a response indicating that the elevator call has been queued
            return $this->success(
                'Elevator call has been queued.',
                [
                    'call_id' => $pendingCall->id
                ]
            );
        }

        // Elevator is in idle state, proceed with the movement

        // Create a new elevator log for the 'moving' state
        $elevatorLog = new ElevatorLog([
            'elevator_id' => $elevator->id,
            'user_id' => auth()->id(),
            'current_floor' => $currentFloor,
            'state' => 'moving',
            'direction' => $direction,
            'action' => 'call',
            'details' => ['target_floor' => $targetFloor], // Store the target floor
        ]);
        $elevatorLog->save();

        // Dispatch the MoveElevator job to the queue
        MoveElevator::dispatch($elevatorLog);

        return $this->success('Elevator movement has been initiated.');
    }
}