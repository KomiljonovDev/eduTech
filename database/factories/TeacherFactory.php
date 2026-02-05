<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+998'.fake()->numerify('9########'),
            'payment_percentage' => fake()->randomElement([30, 35, 40, 45, 50]),
            'salary_type' => Teacher::SALARY_TYPE_PERCENT,
            'fixed_salary' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function fixed(float $salary = 3000000): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_type' => Teacher::SALARY_TYPE_FIXED,
            'fixed_salary' => $salary,
            'payment_percentage' => 0,
        ]);
    }

    public function percent(float $percentage = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_type' => Teacher::SALARY_TYPE_PERCENT,
            'payment_percentage' => $percentage,
            'fixed_salary' => 0,
        ]);
    }

    public function hybrid(float $fixedSalary = 2000000, float $percentage = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_type' => Teacher::SALARY_TYPE_HYBRID,
            'fixed_salary' => $fixedSalary,
            'payment_percentage' => $percentage,
        ]);
    }
}
