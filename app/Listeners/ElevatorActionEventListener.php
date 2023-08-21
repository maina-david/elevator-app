<?php

namespace App\Listeners;

use App\Events\ElevatorActionEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElevatorActionEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ElevatorActionEvent $event): void
    {
        //
    }
}
