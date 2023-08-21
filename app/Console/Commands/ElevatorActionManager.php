<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ElevatorActionManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elevator:action-manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Elevator actions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->startQueueWork();
    }

    protected function startQueueWork()
    {
        $queueName = "elevator_action";

        // Check if the queue is already being worked on
        if (!$this->isQueueBeingWorked($queueName)) {
            $this->info("Starting queue:work for elevator actions...");

            // Start queue:work for the specific elevator queue in the background
            shell_exec("nohup php artisan queue:work --queue={$queueName} > /dev/null 2>&1 &");
        } else {
            $this->info("Queue:work for elevator actions is already running.");
        }
    }

    protected function isQueueBeingWorked($queueName)
    {
        // Check if queue:work process for the specified queue is running
        $output = shell_exec("ps aux | grep '[q]ueue:work --queue={$queueName}'");

        return count(explode(PHP_EOL, $output)) > 1;
    }
}