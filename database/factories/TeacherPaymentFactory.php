<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherPayment>
 */
class TeacherPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'amount' => fake()->numberBetween(500000, 5000000),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'period' => now()->format('Y-m'),
            'method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'notes' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }

    public function forPeriod(string $period): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => $period,
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'cash',
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'card',
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'transfer',
        ]);
    }
}
