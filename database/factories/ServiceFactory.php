<?php

namespace Database\Factories;

use App\Enums\PriceUnit;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'base_price' => fake()->numberBetween(50_000, 2_000_000),
            'price_unit' => PriceUnit::PerJob,
            'duration_estimate' => fake()->randomElement(['1-2 jam', '2-4 jam', '1 hari']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
