<?php

namespace Database\Factories;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'lesson_number' => fake()->numberBetween(1, 48),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'present' => fake()->boolean(80), // 80% attendance rate
            'notes' => fake()->optional(0.1)->sentence(),
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'present' => true,
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'present' => false,
        ]);
    }
}
