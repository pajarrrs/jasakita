<?php

namespace Database\Factories;

use App\Enums\RequestMode;
use App\Enums\RequestStatus;
use App\Models\PartnerProfile;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'service_id' => Service::factory(),
            'partner_profile_id' => null,
            'mode' => RequestMode::Quote,
            'status' => RequestStatus::Open,
            'title' => Str::title(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'city' => fake()->randomElement(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan']),
            'scheduled_at' => null,
            'agreed_price' => null,
            'cancellation_reason' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the customer ordered directly without haggling.
     */
    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => RequestMode::Direct,
        ]);
    }

    /**
     * Indicate that a partner has been assigned to the request.
     */
    public function assigned(?PartnerProfile $partner = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::Assigned,
            'partner_profile_id' => $partner?->id ?? PartnerProfile::factory(),
            'agreed_price' => fake()->numberBetween(50_000, 2_000_000),
        ]);
    }

    /**
     * Indicate that the job has a confirmed schedule.
     */
    public function scheduled(?PartnerProfile $partner = null): static
    {
        return $this->assigned($partner)->state(fn (array $attributes) => [
            'status' => RequestStatus::Scheduled,
            'scheduled_at' => now()->addDays(2)->setTime(9, 0),
        ]);
    }

    /**
     * Indicate that the partner has started working.
     */
    public function inProgress(?PartnerProfile $partner = null): static
    {
        return $this->scheduled($partner)->state(fn (array $attributes) => [
            'status' => RequestStatus::InProgress,
            'scheduled_at' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the job is finished.
     */
    public function completed(?PartnerProfile $partner = null): static
    {
        return $this->inProgress($partner)->state(fn (array $attributes) => [
            'status' => RequestStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the request was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::Cancelled,
            'cancellation_reason' => 'Rencana berubah.',
        ]);
    }
}
