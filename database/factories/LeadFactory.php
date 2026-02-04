<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
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
            'home_phone' => fake()->optional()->numerify('+998#########'),
            'course_id' => Course::factory(),
            'source' => fake()->randomElement(['instagram', 'telegram', 'referral', 'walk_in', 'grand', 'other']),
            'status' => fake()->randomElement(['new', 'contacted', 'interested', 'enrolled', 'not_interested', 'no_answer']),
            'preferred_time' => fake()->optional()->randomElement(['ertalab', 'kunduzi', 'kechqurun']),
            'notes' => fake()->optional()->sentence(),
            'contacted_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'converted_student_id' => null,
        ];
    }

    public function newLead(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'contacted_at' => null,
        ]);
    }

    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function enrolled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'enrolled',
        ]);
    }
}
