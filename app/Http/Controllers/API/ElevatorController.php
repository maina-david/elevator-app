<?php

namespace App\Http\Controllers\API;

use App\Events\ElevatorActionEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingResource;
use App\Jobs\MoveElevator;
use App\Models\Building;
use App\Models\Elevator;
use App\Models\ElevatorLog;
use App\Models\PendingElevatorCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElevatorController extends Controller
{

    public function createBuildingWithElevators(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:buildings,name',
            'number_of_floors' => 'required|numeric',
            'elevators' => 'array',
            'elevators.*.name' => 'required|string',
            'elevators.*.active' => 'boolean'
        ]);

        $building = Building::create([
            'name' => $request->name,
            'number_of_floors' => $request->number_of_floors
        ]);

        foreach ($request->elevators as $elevator) {
            $building->elevators()->create($elevator);
        }

        return $this->success('Building with elevators created successfully', new BuildingResource($building));
    }

    public function listBuildingsWithElevators(): JsonResponse
    {
        $buildings = Building::with('elevators')->get();

        return $this->success('Buildings retrieved successfully', BuildingResource::collection($buildings));
    }

    public function createElevator(Building $building, Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'active' => 'boolean'
        ]);

        $elevator = $building->elevators()->create($request->only('name', 'active'));

        return $this->success('Elevator created successfully!', ['elevator' => $elevator]);
    }

    public function callElevator(Elevator $elevator, Request $request)
    {
        $building = $elevator->building;

        $request->validate([
            'target_floor' => [
                'required',
                'integer',
                "between:1,$building->number_of_floors"
            ]
        ]);

        $targetFloor = $request->target_floor;
        $elevatorLog = $elevator->logs()->latest()->first()->current_floor;
        $currentFloor = $elevator->logs()->latest()->first()->current_floor;
        $currentState = $elevator->logs()->latest()->first()->state;
        $direction = ($targetFloor > $currentFloor) ? 'up' : 'down';

        // Check if elevator is not in idle state
        if ($currentState !== 'idle') {
            // Store the elevator call as a pending call
            $pendingCall = PendingElevatorCall::create([
                'elevator_id' => $elevator->id,
                'target_floor' => $targetFloor
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

        // Create a new elevator log
        $elevatorLog = new ElevatorLog([
            'elevator_id' => $elevator->id,
            'user_id' => auth()->id(),
            'current_floor' => $currentFloor,
            'state' => $currentState,
            'direction' => $direction,
            'action' => 'call',
            'details' => json_encode(['target_floor' => $targetFloor]),
        ]);
        $elevatorLog->save();

        // Dispatch the MoveElevator job to the queue
        MoveElevator::dispatch($elevatorLog);

        return $this->success('Elevator movement has been initiated.');
    }
}
