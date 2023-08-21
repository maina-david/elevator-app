<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Elevator;

class ElevatorQueueManager extends Command
{
    protected $signature = 'elevator:queue-manager';
    protected $description = 'Manage elevator queues';

    public function handle()
    {
        // Keep the command running
        while (true) {
            // Initial elevator collection
            $elevators = Elevator::all();

            // Start queue:work for each elevator
            foreach ($elevators as $elevator) {
                $this->startQueueWork($elevator);
            }

            // Listen for new elevators
            Elevator::created(function ($elevator) {
                $this->startQueueWork($elevator);
            });

            sleep(60);
        }
    }

    protected function startQueueWork(Elevator $elevator)
    {
        $queueName = "elevator_{$elevator->id}";

        // Check if the queue is already being worked on
        if (!$this->isQueueBeingWorked($queueName)) {
            $this->info("Starting queue:work for elevator {$elevator->id}...");

            // Start queue:work for the specific elevator queue
            shell_exec("php artisan queue:work --queue={$queueName} --daemon > /dev/null 2>&1 &");
        }
    }

    protected function isQueueBeingWorked($queueName)
    {
        // Check if queue:work process for the specified queue is running
        $output = shell_exec("ps aux | grep '[q]ueue:work --queue={$queueName}'");

        return count(explode(PHP_EOL, $output)) > 1;
    }
}