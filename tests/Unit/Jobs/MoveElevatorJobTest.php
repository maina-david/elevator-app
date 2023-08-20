<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\MoveElevator;
use App\Models\Building;
use App\Models\Elevator;
use App\Models\ElevatorLog;
use App\Models\PendingElevatorCall;
use Illuminate\Support\Facades\Queue;

class MoveElevatorJobTest extends TestCase
{
    use RefreshDatabase;

    // public function test_it_handles_initial_idle_state_and_same_floor_call_with_no_pending_calls()
    // {
    //     Queue::fake();

    //     // Create a building
    //     $building = Building::factory()->create();

    //     // Create an elevator associated with the building
    //     $elevator = Elevator::factory()->create([
    //         'building_id' => $building->id,
    //         'active' => true,
    //     ]);

    //     // Create a new elevator log with target floor
    //     $newLog = ElevatorLog::factory()->create([
    //         'elevator_id' => $elevator->id,
    //         'current_floor' => $elevator->logs()->latest()->first()->current_floor,
    //         'state' => $elevator->logs()->latest()->first()->state,
    //         'action' => 'call',
    //         'details' => json_encode([
    //             'target_floor' => $elevator->logs()->latest()->first()->current_floor,
    //         ]),
    //     ]);

    //     // Create a new MoveElevator job instance using the new elevator log
    //     $job = new MoveElevator($newLog);

    //     // Execute the job
    //     $job->handle();

    //     // Fetch the latest logs
    //     $elevatorLogs = $elevator->logs()->get();

    //     // Assert the elevator logs for same-floor call
    //     $this->assertEquals('idle', $elevatorLogs[0]->state);
    //     $this->assertEquals('idle', $elevatorLogs[1]->state);

    //     // Assert that the elevator opens and closes doors
    //     $this->assertEquals('doors_opening', $elevatorLogs[2]->state);
    //     $this->assertEquals('doors_open', $elevatorLogs[3]->state);
    //     $this->assertEquals('doors_closing', $elevatorLogs[4]->state);
    //     $this->assertEquals('doors_closed', $elevatorLogs[5]->state);

    //     // Assert that the elevator is idle after completing the task
    //     $this->assertEquals('idle', $elevatorLogs[6]->state);
    // }

    // public function test_it_handles_initial_idle_state_and_first_elevator_call_with_no_pending_calls()
    // {
    //     // Fake the Queue for testing
    //     Queue::fake();

    //     // Create a building
    //     $building = Building::factory()->create([
    //         'number_of_floors' => 10,
    //     ]);

    //     // Create an elevator associated with the building
    //     $elevator = Elevator::factory()->create([
    //         'building_id' => $building->id,
    //         'active' => true,
    //     ]);

    //     // Create a new elevator log with target floor
    //     $newLog = ElevatorLog::factory()->create([
    //         'elevator_id' => $elevator->id,
    //         'current_floor' => $elevator->logs()->latest()->first()->current_floor,
    //         'state' => $elevator->logs()->latest()->first()->state,
    //         'action' => 'call',
    //         'details' => json_encode([
    //             'target_floor' => 10,
    //         ]),
    //     ]);

    //     // Create a new MoveElevator job instance using the new elevator log
    //     $job = new MoveElevator($newLog);

    //     // Execute the job
    //     $job->handle();
    //     // Fetch the latest logs
    //     $elevatorLogs = $elevator->logs()->get();

    //     // Assert the elevator logs based on the provided logs
    //     $this->assertEquals('idle', $elevatorLogs[0]->state);
    //     $this->assertEquals('idle', $elevatorLogs[1]->state);

    //     // Assert that the elevator starts moving
    //     $this->assertEquals('moving', $elevatorLogs[2]->state);

    //     // Assert that the elevator continues moving until it reaches the target floor
    //     for ($i = 1; $i <= 8; $i++) {
    //         $this->assertEquals('moving', $elevatorLogs[$i + 2]->state);
    //     }

    //     // Assert that the elevator stops moving once it reaches the target floor
    //     $this->assertEquals('stopped', $elevatorLogs[11]->state);

    //     // Assert the door states
    //     $this->assertEquals('doors_opening', $elevatorLogs[12]->state);
    //     $this->assertEquals('doors_open', $elevatorLogs[13]->state);
    //     $this->assertEquals('doors_closing', $elevatorLogs[14]->state);
    //     $this->assertEquals('doors_closed', $elevatorLogs[15]->state);

    //     // Assert that the elevator is idle after completing the task
    //     $this->assertEquals('idle', $elevatorLogs[16]->state);
    // }

    // public function test_it_handles_initial_idle_state_and_first_elevator_call_with_one_pending_call()
    // {
    //     // Fake the Queue for testing
    //     Queue::fake();

    //     // Create a building
    //     $building = Building::factory()->create([
    //         'number_of_floors' => 10,
    //     ]);

    //     // Create an elevator associated with the building
    //     $elevator = Elevator::factory()->create([
    //         'building_id' => $building->id
    //     ]);

    //     $targetFloor = 10;
    //     $pendingCallTargetFloor = 5;

    //     // Create a new elevator log with target floor
    //     $newLog = ElevatorLog::factory()->create([
    //         'elevator_id' => $elevator->id,
    //         'current_floor' => $elevator->logs()->latest()->first()->current_floor,
    //         'state' => $elevator->logs()->latest()->first()->state,
    //         'action' => 'call',
    //         'details' => json_encode([
    //             'target_floor' => $targetFloor,
    //         ]),
    //     ]);

    //     // Create a pending elevator call
    //     $pendingElevatorCall = PendingElevatorCall::factory()->create([
    //         'elevator_id' => $elevator->id,
    //         'target_floor' => $pendingCallTargetFloor
    //     ]);

    //     // Create a new MoveElevator job instance using the new elevator log
    //     $job = new MoveElevator($newLog);

    //     // Execute the job
    //     $job->handle();

    //     // Fetch the latest logs
    //     $elevatorLogs = $elevator->logs()->get();

    //     // Assert the elevator actions and states
    //     $this->assertEquals('idle', $elevatorLogs[0]->state);
    //     $this->assertEquals('call', $elevatorLogs[1]->action);
    //     $this->assertEquals('moving', $elevatorLogs[2]->state);

    //     // Assert that the elevator continues moving until it reaches the pending call floor
    //     for ($i = 1; $i <= 4; $i++) {
    //         $this->assertEquals('moving', $elevatorLogs[$i + 2]->state);
    //     }

    //     // Assert that the elevator stops at the pending call floor
    //     $this->assertEquals('stopped', $elevatorLogs[7]->state);

    //     // Assert the door states after stopping at pending call floor
    //     $this->assertEquals('doors_opening', $elevatorLogs[8]->state);
    //     $this->assertEquals('doors_open', $elevatorLogs[9]->state);
    //     $this->assertEquals('doors_closing', $elevatorLogs[10]->state);
    //     $this->assertEquals('doors_closed', $elevatorLogs[11]->state);

    //     // Assert that the elevator continues moving to the target floor
    //     for ($i = 1; $i <= 4; $i++) {
    //         $this->assertEquals('moving', $elevatorLogs[$i + 11]->state);
    //     }

    //     // Assert that the elevator stops at the target floor
    //     $this->assertEquals('stopped', $elevatorLogs[16]->state);

    //     // Assert the door states after stopping at target floor
    //     $this->assertEquals('doors_opening', $elevatorLogs[17]->state);
    //     $this->assertEquals('doors_open', $elevatorLogs[18]->state);
    //     $this->assertEquals('doors_closing', $elevatorLogs[19]->state);
    //     $this->assertEquals('doors_closed', $elevatorLogs[20]->state);

    //     // Assert that the elevator is idle after completing the task
    //     $this->assertEquals('idle', $elevatorLogs[21]->state);

    //     // Assert that the pending call is executed
    //     $this->assertEquals(true, $pendingElevatorCall->fresh()->executed);
    // }


    public function test_it_handles_multiple_pending_calls()
    {
        // Fake the Queue for testing
        Queue::fake();

        // Create a building
        $building = Building::factory()->create([
            'number_of_floors' => 10,
        ]);

        // Create an elevator associated with the building
        $elevator = Elevator::factory()->create([
            'building_id' => $building->id
        ]);

        // Create an elevator log with target floor
        $newLog = ElevatorLog::factory()->create([
            'elevator_id' => $elevator->id,
            'current_floor' => $elevator->logs()->latest()->first()->current_floor,
            'state' => $elevator->logs()->latest()->first()->state,
            'action' => 'call',
            'details' => json_encode([
                'target_floor' => 10,
            ]),
        ]);

        // Create multiple pending elevator calls
        $pendingCallTargetFloors = [5, 7, 3];
        foreach ($pendingCallTargetFloors as $floor) {
            PendingElevatorCall::factory()->create([
                'elevator_id' => $elevator->id,
                'target_floor' => $floor
            ]);
        }

        // Create a new MoveElevator job instance using the new elevator log
        $job = new MoveElevator($newLog);

        // Execute the job
        $job->handle();

        // Fetch the latest logs
        $elevatorLogs = $elevator->logs()->get();

        // Assert database log count
        $this->assertCount(32, $elevatorLogs);

        // Assert elevator states and actions
        $expectedStates = [
            'idle',
            'idle',
            'moving',
            'moving',
            'moving',
            'stopped',
            'doors_opening',
            'doors_open',
            'doors_closing',
            'doors_closed',
            'moving',
            'moving',
            'stopped',
            'doors_opening',
            'doors_open',
            'doors_closing',
            'doors_closed',
            'moving',
            'moving',
            'stopped',
            'doors_opening',
            'doors_open',
            'doors_closing',
            'doors_closed',
            'moving',
            'moving',
            'stopped',
            'doors_opening',
            'doors_open',
            'doors_closing',
            'doors_closed',
            'idle'
        ];

        foreach ($elevatorLogs as $index => $log) {
            $this->assertEquals($expectedStates[$index], $log->state);
        }
    }
}