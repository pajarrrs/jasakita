<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\PartnerProfile;
use App\Models\PartnerVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerVerification>
 */
class PartnerVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_profile_id' => PartnerProfile::factory(),
            'full_name' => fake()->name(),
            'id_number' => fake()->numerify('################'),
            'id_photo_path' => 'kyc/ktp-contoh.jpg',
            'selfie_photo_path' => 'kyc/selfie-contoh.jpg',
            'status' => VerificationStatus::Pending,
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Verifikasi yang sudah disetujui admin.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VerificationStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Verifikasi yang ditolak admin.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VerificationStatus::Rejected,
            'rejection_reason' => 'Foto KTP kurang jelas.',
            'reviewed_at' => now(),
        ]);
    }
}
