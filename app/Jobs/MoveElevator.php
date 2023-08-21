<?php

namespace App\Jobs;

use App\Events\ElevatorActionEvent;
use App\Models\ElevatorLog;
use App\Models\PendingElevatorCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MoveElevator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $elevatorLog;
    protected $floorTravelTime;
    protected $doorOpenCloseTime;

    /**
     * Create a new job instance.
     *
     * @param ElevatorLog $elevatorLog
     * @return void
     */
    public function __construct(ElevatorLog $elevatorLog)
    {
        $this->elevatorLog = $elevatorLog;
        $this->floorTravelTime = 5; // seconds
        $this->doorOpenCloseTime = 2; // seconds
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $details = json_decode($this->elevatorLog->details, true);
        $targetFloor = $details['target_floor'] ?? null;

        // Check if the target floor is the same as the current floor
        if (
            $this->elevatorLog->state === 'idle' &&
            $this->elevatorLog->action === 'call' &&
            $targetFloor === $this->elevatorLog->current_floor
        ) {
            // Open and close the doors
            $this->simulateDoorsOpening($this->elevatorLog->current_floor);
            $this->simulateDoorsClosing($this->elevatorLog->current_floor);
        } else {
            // Check if the elevator is idle and there is a target floor
            if (
                $targetFloor !== null &&
                $targetFloor !== $this->elevatorLog->current_floor &&
                $this->elevatorLog->state === 'idle'
            ) {
                // Move to the target floor
                $this->simulateMovement($targetFloor);
            }
        }

        $this->handlePendingCalls();
    }

    /**
     * Log an elevator action.
     *
     * @param string $state
     * @param int|null $targetFloor
     * @return void
     */
    protected function logAction($state, $currentFloor, $direction = null, $targetFloor = null): void
    {
        $validStates = [
            'idle',
            'moving',
            'stopped',
            'doors_opening',
            'doors_open',
            'doors_closing',
            'doors_closed'
        ];

        if (!in_array($state, $validStates)) {
            // Default to 'idle' if the state is not recognized
            $state = 'idle';
        }

        $details = $this->getActionDetails($state, $targetFloor);

        $elevatorLog = new ElevatorLog([
            'elevator_id' => $this->elevatorLog->elevator_id,
            'user_id' => $this->elevatorLog->user_id,
            'current_floor' => $currentFloor,
            'state' => $state,
            'direction' => $direction,
            'action' => $state,
            'details' => $details,
        ]);
        $elevatorLog->save();

        ElevatorActionEvent::dispatch($elevatorLog);
    }

    /**
     * Get details for the action being performed.
     *
     * @param string $state
     * @param int|null $targetFloor
     * @return array
     */
    protected function getActionDetails($state, $targetFloor = null)
    {
        $details = [];

        if ($state === 'moving' || $state === 'stopped') {
            if ($targetFloor !== null) {
                $details['target_floor'] = $targetFloor;
            }
        }

        if ($state === 'doors_opening' || $state === 'doors_open' || $state === 'doors_closing' || $state === 'doors_closed') {
            $details['door_state'] = $state;
        }

        if ($state === 'idle') {
            $details['pending_calls'] = $this->getPendingCallsDetails();

            // If there are no pending calls, indicate that the doors are closed
            if (empty($details['pending_calls'])) {
                $details['door_state'] = 'doors_closed';
            }
        }

        return json_encode($details);
    }

    /**
     * Get details for pending elevator calls.
     *
     * @return array
     */
    protected function getPendingCallsDetails()
    {
        $pendingCalls = PendingElevatorCall::where('elevator_id', $this->elevatorLog->elevator_id)
            ->where('executed', false)
            ->orderBy('created_at')
            ->get();

        $pendingCallsDetails = [];

        foreach ($pendingCalls as $pendingCall) {
            $pendingCallsDetails[] = [
                'target_floor' => $pendingCall->target_floor,
                'created_at' => $pendingCall->created_at,
            ];
        }

        return $pendingCallsDetails;
    }

    /**
     * Handle pending calls.
     *
     * @return void
     */
    protected function handlePendingCalls(): void
    {
        // Get the last elevator log created
        $elevatorLog = ElevatorLog::where('elevator_id', $this->elevatorLog->elevator_id)
            ->latest()
            ->first();

        // Count if there are any pending elevator calls
        $pendingCalls = PendingElevatorCall::where('elevator_id', $this->elevatorLog->elevator_id)
            ->where('executed', false)
            ->count();

        // Initialize direction to determine if the elevator will actually move
        $direction = NULL;

        // Execute only if there are pending elevator calls
        if ($pendingCalls > 0) {
            // Get the first call action created in the pending call
            $firstPendingCall = PendingElevatorCall::where('elevator_id', $this->elevatorLog->elevator_id)
                ->where('executed', false)
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($elevatorLog->current_floor > $firstPendingCall->target_floor) {
                $direction = 'down';
            } else if ($elevatorLog->current_floor < $firstPendingCall->target_floor) {
                $direction = 'up';
            }
            $lastPendingCall = PendingElevatorCall::where('elevator_id', $this->elevatorLog->elevator_id)
                ->where('executed', false)
                ->orderBy('created_at', 'ASC')
                ->first();

            // The elevator does not move
            if (!$direction && $lastPendingCall->id === $firstPendingCall->id) {
                $this->simulateDoorsOpening($elevatorLog->current_floor);
                $this->simulateDoorsClosing($elevatorLog->current_floor);
                $this->logAction('idle', $elevatorLog->current_floor);
                return;
            } else if ($direction) {
                $this->simulateMovement($firstPendingCall->target_floor);
            }
        } else {
            $this->logAction('idle', $elevatorLog->current_floor);
        }
    }

    /**
     * Simulate elevator movement.
     *
     * @param int $targetFloor
     * @return void
     */
    protected function simulateMovement($targetFloor): void
    {
        $elevatorLog = ElevatorLog::where('elevator_id', $this->elevatorLog->elevator_id)
            ->latest()
            ->first();

        $currentFloor = $elevatorLog->current_floor;

        // Determine the direction of movement
        $direction = ($targetFloor > $currentFloor) ? 'up' : 'down';

        // Calculate floors to move based on direction
        $floorsToMove = abs($targetFloor - $currentFloor);

        // Log the elevator's initial moving action
        $this->logAction('moving', $currentFloor, $direction, $targetFloor);

        for ($i = 1; $i <= $floorsToMove; $i++) {
            if ($direction === 'up') {
                $currentFloor++;
            } else {
                $currentFloor--;
            }

            // Log the elevator's current action (moving or stopped)
            if ($currentFloor !== $targetFloor) {
                $this->logAction('moving', $currentFloor, $direction, $targetFloor);
                sleep($this->floorTravelTime);
            } else {
                $this->logAction('stopped', $currentFloor, $direction, $targetFloor);
                $this->simulateDoorsOpening($currentFloor);
                $this->simulateDoorsClosing($currentFloor);
            }

            // Check if there are any pending calls at the current floor
            foreach ($this->getPendingCallsInDirection($currentFloor, $direction) as $pendingCall) {
                if ($currentFloor === $pendingCall->target_floor) {
                    // Stop the elevator and execute the pending call
                    $this->logAction('stopped', $currentFloor, $direction, $targetFloor);
                    $this->simulateDoorsOpening($currentFloor);
                    $this->simulateDoorsClosing($currentFloor);

                    // Mark the pending call as executed
                    $pendingCall->update([
                        'executed' => true,
                        'execution_duration' => now()->diffInSeconds($pendingCall->created_at)
                    ]);
                }
            }
        }
    }

    /**
     * Get pending elevator calls in a specific direction.
     *
     * @param int $currentFloor The current floor of the elevator.
     * @param string $direction The direction of movement ('up' or 'down').
     * @return \Illuminate\Database\Eloquent\Collection|PendingElevatorCall[]
     */
    protected function getPendingCallsInDirection($currentFloor, $direction): \Illuminate\Database\Eloquent\Collection
    {
        $comparisonOperator = ($direction === 'up') ? '>=' : '<=';

        return PendingElevatorCall::where('elevator_id', $this->elevatorLog->elevator_id)
            ->where('executed', false)
            ->where('target_floor', $comparisonOperator, $currentFloor)
            ->orderBy('target_floor', ($direction === 'up') ? 'asc' : 'desc')
            ->get();
    }

    /**
     * Simulate doors opening.
     *
     * @return void
     */
    protected function simulateDoorsOpening($currentFloor): void
    {
        $this->logAction('doors_opening', $currentFloor);
        sleep($this->doorOpenCloseTime);
        $this->logAction('doors_open', $currentFloor);
    }

    /**
     * Simulate doors closing.
     *
     * @return void
     */
    protected function simulateDoorsClosing($currentFloor): void
    {
        $this->logAction('doors_closing', $currentFloor);
        sleep($this->doorOpenCloseTime);
        $this->logAction('doors_closed', $currentFloor);
    }
}