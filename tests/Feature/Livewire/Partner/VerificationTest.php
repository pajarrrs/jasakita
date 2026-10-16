<?php

namespace Tests\Feature\Livewire\Partner;

use App\Enums\VerificationStatus;
use App\Livewire\Admin\Verifications\Index as AdminVerificationsIndex;
use App\Livewire\Partner\Verification;
use App\Models\Offer;
use App\Models\PartnerProfile;
use App\Models\PartnerVerification;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\VerificationReviewed;
use App\Notifications\VerificationSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_partner_can_submit_identity_verification(): void
    {
        Storage::fake('public');

        $profile = PartnerProfile::factory()->create();
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($profile->user)
            ->test(Verification::class)
            ->set('fullName', 'Budi Santoso')
            ->set('idNumber', '3273010101900001')
            ->set('idPhoto', UploadedFile::fake()->image('ktp.jpg'))
            ->set('selfiePhoto', UploadedFile::fake()->image('selfie.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $verification = PartnerVerification::sole();

        $this->assertSame(VerificationStatus::Pending, $verification->status);
        $this->assertSame('Budi Santoso', $verification->full_name);
        $this->assertSame('3273010101900001', $verification->id_number);
        Storage::disk('public')->assertExists($verification->id_photo_path);
        Storage::disk('public')->assertExists($verification->selfie_photo_path);

        $this->assertFalse($profile->fresh()->isVerified());
        $this->assertTrue(
            $admin->notifications()->where('type', VerificationSubmitted::class)->exists(),
        );
    }

    public function test_the_identity_photos_are_required_on_the_first_submission(): void
    {
        Storage::fake('public');

        $profile = PartnerProfile::factory()->create();

        Livewire::actingAs($profile->user)
            ->test(Verification::class)
            ->set('fullName', 'Budi Santoso')
            ->set('idNumber', '3273010101900001')
            ->call('submit')
            ->assertHasErrors(['idPhoto' => 'required', 'selfiePhoto' => 'required']);

        $this->assertDatabaseCount('partner_verifications', 0);
    }

    public function test_a_pending_submission_cannot_be_sent_again(): void
    {
        $profile = PartnerProfile::factory()->create();
        PartnerVerification::factory()->create(['partner_profile_id' => $profile->id]);

        Livewire::actingAs($profile->user)
            ->test(Verification::class)
            ->set('fullName', 'Nama Berbeda')
            ->call('submit');

        $this->assertDatabaseCount('partner_verifications', 1);
        $this->assertNotSame('Nama Berbeda', PartnerVerification::sole()->full_name);
    }

    public function test_a_rejected_partner_can_resubmit_without_reuploading_the_photos(): void
    {
        Storage::fake('public');

        $profile = PartnerProfile::factory()->create();
        $verification = PartnerVerification::factory()->rejected()->create([
            'partner_profile_id' => $profile->id,
            'id_photo_path' => 'kyc/ktp-lama.jpg',
        ]);

        Livewire::actingAs($profile->user)
            ->test(Verification::class)
            ->set('fullName', 'Budi Santoso')
            ->set('idNumber', '3273010101900001')
            ->call('submit')
            ->assertHasNoErrors();

        $verification->refresh();

        $this->assertSame(VerificationStatus::Pending, $verification->status);
        $this->assertNull($verification->rejection_reason);
        $this->assertSame('kyc/ktp-lama.jpg', $verification->id_photo_path);
    }

    public function test_the_verification_page_shows_the_current_status(): void
    {
        $profile = PartnerProfile::factory()->create();
        PartnerVerification::factory()->approved()->create(['partner_profile_id' => $profile->id]);

        $this->actingAs($profile->user)
            ->get(route('partner.verification'))
            ->assertOk()
            ->assertSee('Terverifikasi')
            ->assertDontSee('Formulir Verifikasi');
    }

    public function test_a_verified_partner_shows_the_verified_badge_on_offers(): void
    {
        $profile = PartnerProfile::factory()->create();
        PartnerVerification::factory()->approved()->create(['partner_profile_id' => $profile->id]);

        $serviceRequest = ServiceRequest::factory()->create();
        Offer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'partner_profile_id' => $profile->id,
        ]);

        $this->actingAs($serviceRequest->customer)
            ->get(route('customer.requests.show', $serviceRequest))
            ->assertOk()
            ->assertSee('Terverifikasi');
    }

    public function test_an_admin_can_approve_a_submission(): void
    {
        $admin = User::factory()->admin()->create();
        $verification = PartnerVerification::factory()->create();

        Livewire::actingAs($admin)
            ->test(AdminVerificationsIndex::class)
            ->call('inspect', $verification->id)
            ->assertSet('showDetail', true)
            ->call('approve');

        $verification->refresh();

        $this->assertSame(VerificationStatus::Approved, $verification->status);
        $this->assertSame($admin->id, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);
        $this->assertTrue($verification->partner->isVerified());
        $this->assertTrue(
            $verification->partner->user->notifications()->where('type', VerificationReviewed::class)->exists(),
        );
    }

    public function test_an_admin_must_give_a_reason_to_reject_a_submission(): void
    {
        $admin = User::factory()->admin()->create();
        $verification = PartnerVerification::factory()->create();

        Livewire::actingAs($admin)
            ->test(AdminVerificationsIndex::class)
            ->call('inspect', $verification->id)
            ->call('reject')
            ->assertHasErrors(['rejectionReason' => 'required'])
            ->set('rejectionReason', 'Foto KTP buram, mohon unggah ulang.')
            ->call('reject')
            ->assertHasNoErrors();

        $verification->refresh();

        $this->assertSame(VerificationStatus::Rejected, $verification->status);
        $this->assertSame('Foto KTP buram, mohon unggah ulang.', $verification->rejection_reason);
        $this->assertFalse($verification->partner->isVerified());
    }

    public function test_a_partner_cannot_open_the_admin_verification_page(): void
    {
        $profile = PartnerProfile::factory()->create();

        $this->actingAs($profile->user)
            ->get(route('admin.verifications.index'))
            ->assertForbidden();
    }
}
