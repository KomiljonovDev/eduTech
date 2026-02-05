<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
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
            'address' => fake()->optional()->address(),
            'source' => fake()->randomElement(['instagram', 'telegram', 'referral', 'walk_in', 'grand', 'other']),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Student $student) {
            // Create primary phone
            $student->phones()->create([
                'number' => '+998'.fake()->numerify('9########'),
                'owner' => null,
                'is_primary' => true,
            ]);

            // Optionally create home phone
            if (fake()->boolean(50)) {
                $student->phones()->create([
                    'number' => '+998'.fake()->numerify('#########'),
                    'owner' => 'Uy',
                    'is_primary' => false,
                ]);
            }
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create student with additional extra phones.
     */
    public function withExtraPhones(int $count = 1): static
    {
        return $this->afterCreating(function (Student $student) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $student->phones()->create([
                    'number' => '+998'.fake()->numerify('9########'),
                    'owner' => fake()->randomElement(['Ota', 'Ona', 'Aka', 'Opa', null]),
                    'is_primary' => false,
                ]);
            }
        });
    }
}
