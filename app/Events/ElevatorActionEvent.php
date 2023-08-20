<?php

namespace App\Events;

use App\Models\ElevatorLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ElevatorActionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $elevatorLog;
    /**
     * Create a new event instance.
     */
    public function __construct(ElevatorLog $elevatorLog)
    {
        $this->elevatorLog = $elevatorLog;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('elevator-action'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        $stateMappings = [
            'idle' => 'Idle',
            'moving' => 'Moving',
            'stopped' => 'Stopped',
            'doors_opening' => 'Doors Opening',
            'doors_open' => 'Doors Open',
            'doors_closing' => 'Doors Closing',
            'doors_closed' => 'Doors Closed',
        ];

        $actionMappings = [
            'call' => isset(json_decode($this->elevatorLog->details)->target_floor)
                ? 'Elevator called to Floor ' . json_decode($this->elevatorLog->details)->target_floor
                : 'Elevator called',
            'idle' => 'Idle',
            'moving' => 'Moving',
            'stopped' => 'Stopped',
            'doors_opening' => 'Doors Opening',
            'doors_open' => 'Doors Open',
            'doors_closing' => 'Doors Closing',
            'doors_closed' => 'Doors Closed',
        ];

        $state = $this->elevatorLog->state;
        $action = $this->elevatorLog->action;
        $currentFloor = $this->elevatorLog->current_floor;
        $elevatorBuilding = $this->elevatorLog->elevator->building ?? null;

        if ($elevatorBuilding) {
            $buildingFloors = $elevatorBuilding->number_of_floors;
            $floorNames = [];

            for ($i = 1; $i <= $buildingFloors; $i++) {
                $floorNames[$i] = $this->getReadableFloorName($i);
            }

            // Use the mappings to get human-readable state and action names
            $formattedState = $stateMappings[$state] ?? 'Unknown State';
            $formattedAction = $actionMappings[$action] ?? 'Unknown Action';

            // Determine the final message based on matching state and action
            if ($formattedState === $formattedAction) {
                $formattedMessage = "Elevator at {$floorNames[$currentFloor]} Floor: $formattedState";
            } else {
                $formattedMessage = "Elevator at {$floorNames[$currentFloor]} Floor: $formattedState - $formattedAction";
            }

            return $formattedMessage;
        }

        return 'Elevator Log Broadcast'; // Default message if building info is not available
    }

    /**
     * Get the readable floor name (e.g., 1st, 2nd, 3rd, etc.).
     *
     * @param int $floorNumber
     * @return string
     */
    private function getReadableFloorName($floorNumber)
    {
        $ordinalSuffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        $suffixIndex = $floorNumber % 100;
        $suffix = ($suffixIndex >= 11 && $suffixIndex <= 13) ? 'th' : $ordinalSuffix[$suffixIndex % 10];

        return "{$floorNumber}{$suffix}";
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->elevatorLog->id,
            'elevator_id' => $this->elevatorLog->elevator_id,
            'current_floor' => $this->elevatorLog->current_floor,
            'state' => $this->elevatorLog->state,
            'direction' => $this->elevatorLog->direction,
            'action' => $this->elevatorLog->action,
            'details' => $this->elevatorLog->details,
        ];
    }
}
