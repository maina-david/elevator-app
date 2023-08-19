<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\MoveElevator;
use App\Models\Building;
use App\Models\Elevator;
use App\Models\ElevatorLog;
use Illuminate\Support\Facades\Queue;

class MoveElevatorJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_handles_idle_state_with_no_pending_calls()
    {
        Queue::fake();

        // Create a building
        $building = Building::factory()->create();

        // Create an elevator associated with the building
        $elevator = Elevator::factory()->create([
            'building_id' => $building->id,
            'active' => true,
        ]);

        // Create a new elevator log with target floor 1
        $newLog = ElevatorLog::factory()->create([
            'elevator_id' => $elevator->id,
            'current_floor' => $elevator->logs()->latest()->first()->current_floor,
            'state' => $elevator->logs()->latest()->first()->state,
            'action' => 'call',
            'details' => json_encode([
                'target_floor' => 1,
            ]),
        ]);

        // Create a new MoveElevator job instance using the new elevator log
        $job = new MoveElevator($newLog);

        // Execute the job
        $job->handle();

        // Assertions for elevator logs, door states, and idle state

        // Assert that the correct number of elevator logs are created
        $this->assertDatabaseCount('elevator_logs', 7);

        $elevatorLogs = $elevator->logs()->take(7)->get();

        $this->assertEquals('idle', $elevatorLogs[0]->state);
        $this->assertEquals('idle', $elevatorLogs[1]->state);
        $this->assertEquals('doors_opening', $elevatorLogs[2]->state);
        $this->assertEquals('doors_open', $elevatorLogs[3]->state);
        $this->assertEquals('doors_closing', $elevatorLogs[4]->state);
        $this->assertEquals('doors_closed', $elevatorLogs[5]->state);
        $this->assertEquals('idle', $elevatorLogs[6]->state);
    }
}