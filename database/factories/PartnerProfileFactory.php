<?php

namespace Database\Factories;

use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerProfile>
 */
class PartnerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->partner(),
            'bio' => fake()->paragraph(),
            'city' => fake()->randomElement(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan']),
            'years_of_experience' => fake()->numberBetween(1, 20),
            'is_available' => true,
        ];
    }

    /**
     * Indicate that the partner is not accepting jobs.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
