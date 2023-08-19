<?php

namespace App\Jobs;

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
    public function handle()
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
            $this->simulateDoorsOpening();
            $this->simulateDoorsClosing();
            $this->logAction('idle');
        } else {
            // Check if the elevator is idle and there is a target floor
            if (
                $targetFloor !== null &&
                $targetFloor !== $this->elevatorLog->current_floor &&
                $this->elevatorLog->state === 'idle'
            ) {
                // Move to the target floor
                $this->simulateMovement($targetFloor);
                $this->simulateDoorsOpening();
                $this->simulateDoorsClosing();
            }
        }
    }



    /**
     * Log an elevator action.
     *
     * @param string $state
     * @param int|null $targetFloor
     * @return void
     */
    protected function logAction($state, $targetFloor = null)
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
            'current_floor' => $this->elevatorLog->current_floor,
            'state' => $state,
            'direction' => null,
            'action' => $state,
            'details' => $details,
        ]);

        $elevatorLog->save();
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

        if ($state === 'moving') {
            $details['target_floor'] = isset($this->elevatorLog->details['target_floor'])
                ? $this->elevatorLog->details['target_floor']
                : null;
        }

        if ($state === 'stopped') {
            if ($targetFloor !== null) {
                $details['target_floor'] = $targetFloor;
            }
            // Doors are closed when the elevator is stopped
            $details['door_state'] = 'doors_closed';
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
     * Check if the elevator should stop at the given floor.
     *
     * @param PendingElevatorCall $pendingCall
     * @return bool
     */
    protected function shouldStopAtFloor(PendingElevatorCall $pendingCall)
    {
        $direction = $this->elevatorLog->direction;

        if ($direction === 'up') {
            return $pendingCall->target_floor <= $this->elevatorLog->current_floor;
        } elseif ($direction === 'down') {
            return $pendingCall->target_floor >= $this->elevatorLog->current_floor;
        }

        return false;
    }

    /**
     * Handle pending calls when the elevator becomes idle.
     *
     * @return void
     */
    protected function handlePendingCallsOnIdle()
    {
        // Check if there are pending calls for the elevator
        $pendingCalls = PendingElevatorCall::where('elevator_id', $this->elevatorLog->elevator_id)
            ->where('executed', false)
            ->orderBy('created_at')
            ->get();

        if ($pendingCalls->count() > 0) {
            // Track the last pending call
            $lastPendingCall = $pendingCalls->last();

            foreach ($pendingCalls as $pendingCall) {
                // Check if the elevator should stop at this floor
                $direction = $pendingCall->target_floor > $this->elevatorLog->current_floor ? 'up' : 'down';
                if ($this->shouldStopAtFloor($pendingCall, $direction)) {
                    // Log the action, stop at the floor, simulate doors, etc.
                    $this->logAction('stopped', $pendingCall->target_floor);
                    $this->simulateDoorsOpening();
                    $this->simulateDoorsClosing();

                    // Mark the pending call as executed and calculate execution duration
                    $pendingCall->update([
                        'executed' => true,
                        'execution_duration' => now()->diffInSeconds($pendingCall->created_at),
                    ]);

                    // If this is the last pending call, update elevator state to idle
                    if ($pendingCall->id === $lastPendingCall->id) {
                        $this->logAction('idle'); // Elevator goes to idle state after last pending call
                    }
                }
            }
        }
    }

    /**
     * Simulate doors opening.
     *
     * @return void
     */
    protected function simulateDoorsOpening()
    {
        $this->logAction('doors_opening');
        sleep($this->doorOpenCloseTime);
        $this->logAction('doors_open');
    }

    /**
     * Simulate doors closing.
     *
     * @return void
     */
    protected function simulateDoorsClosing()
    {
        $this->logAction('doors_closing');
        sleep($this->doorOpenCloseTime); //
        $this->logAction('doors_closed');
    }

    /**
     * Simulate elevator movement.
     *
     * @param int $targetFloor
     * @return void
     */
    protected function simulateMovement($targetFloor)
    {
        $floorsToMove = abs($targetFloor - $this->elevatorLog->current_floor);
        $movementTime = $floorsToMove * $this->floorTravelTime;
        $this->logAction('moving');
        sleep($movementTime);
        $this->logAction('stopped');
        $this->elevatorLog->current_floor = $targetFloor; // Update current floor
    }
}