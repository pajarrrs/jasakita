<?php

namespace Database\Factories;

use App\Models\PartnerProfile;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory()->completed(),
            'customer_id' => User::factory(),
            'partner_profile_id' => PartnerProfile::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->sentence(),
        ];
    }

    /**
     * Ikuti data request beserta pelanggan dan mitranya.
     */
    public function forRequest(ServiceRequest $serviceRequest): static
    {
        return $this->state(fn (array $attributes) => [
            'service_request_id' => $serviceRequest->id,
            'customer_id' => $serviceRequest->customer_id,
            'partner_profile_id' => $serviceRequest->partner_profile_id,
        ]);
    }
}
