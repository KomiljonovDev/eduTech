<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $courses = [
            ['name' => 'Python Dasturlash', 'code' => 'PY'],
            ['name' => 'JavaScript Asoslari', 'code' => 'JS'],
            ['name' => 'Web Dizayn', 'code' => 'WD'],
            ['name' => 'Mobile Dasturlash', 'code' => 'MB'],
            ['name' => 'Data Science', 'code' => 'DS'],
            ['name' => 'Cyber Security', 'code' => 'CS'],
            ['name' => 'Graphic Design', 'code' => 'GD'],
            ['name' => 'SMM Marketing', 'code' => 'SM'],
        ];

        $course = fake()->randomElement($courses);

        return [
            'name' => $course['name'],
            'code' => $course['code'].fake()->unique()->numerify('###'),
            'description' => fake()->optional()->paragraph(),
            'monthly_price' => fake()->randomElement([300000, 400000, 500000, 600000, 700000]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
