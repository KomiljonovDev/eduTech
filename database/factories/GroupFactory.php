<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Room;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        return [
            'name' => fake()->randomElement(['Alpha', 'Beta', 'Gamma', 'Delta']).'-'.fake()->unique()->numberBetween(1, 100),
            'course_id' => Course::factory(),
            'teacher_id' => Teacher::factory(),
            'room_id' => Room::factory(),
            'days' => fake()->randomElement(['odd', 'even']),
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
            'total_lessons' => fake()->numberBetween(24, 48),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => fake()->randomElement(['pending', 'active', 'completed', 'cancelled']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
