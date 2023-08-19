<?php

namespace Database\Factories;

use App\Models\Elevator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ElevatorLog>
 */
class ElevatorLogFactory extends Factory
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
            'user_id' => User::factory(), // Generate a valid user ID using the factory
            'current_floor' => $this->faker->numberBetween(1, 10), // Replace with a valid range
            'state' => 'idle', // Default state
            'direction' => null, // Default direction
            'action' => null, // Default action
            'details' => null, // Default details
        ];
    }
}