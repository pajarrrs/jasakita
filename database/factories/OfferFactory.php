<?php

namespace Database\Factories;

use App\Enums\OfferStatus;
use App\Models\Offer;
use App\Models\PartnerProfile;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'partner_profile_id' => PartnerProfile::factory(),
            'price' => fake()->numberBetween(50_000, 2_000_000),
            'duration_estimate' => fake()->randomElement(['1-2 jam', '2-4 jam', '1 hari']),
            'message' => fake()->sentence(),
            'status' => OfferStatus::Pending,
        ];
    }

    /**
     * Indicate that the customer accepted the offer.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::Accepted,
        ]);
    }

    /**
     * Indicate that the offer was rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::Rejected,
        ]);
    }
}
