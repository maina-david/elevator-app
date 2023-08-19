<?php

namespace Database\Factories;

use App\Models\Elevator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PendingElevatorCall>
 */
class PendingElevatorCallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'elevator_id' => Elevator::factory(), // Generate a valid elevator ID using the factory
            'target_floor' => $this->faker->numberBetween(1, 10), // Replace with a valid range
            'executed' => false, // Default executed state
            'execution_duration' => null, // Default execution duration
        ];
    }
}