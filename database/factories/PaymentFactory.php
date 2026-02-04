<?php

namespace Database\Factories;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomElement([300000, 400000, 500000, 600000, 700000]);

        return [
            'enrollment_id' => Enrollment::factory(),
            'amount' => $amount,
            'teacher_share' => null, // Will be calculated by model boot
            'school_share' => null, // Will be calculated by model boot
            'paid_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'period' => fake()->date('Y-m'),
            'method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'notes' => fake()->optional()->sentence(),
        ];
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
