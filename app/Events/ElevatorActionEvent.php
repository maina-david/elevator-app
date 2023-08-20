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
        return 'Elevator action: ' . $this->elevatorLog->action;
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
