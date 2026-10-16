<?php

namespace Tests\Feature;

use App\Livewire\Customer\Requests\Show as CustomerRequestShow;
use App\Livewire\Partner\Jobs\Show as PartnerJobShow;
use App\Livewire\Partner\Requests\Show as PartnerRequestShow;
use App\Models\Offer;
use App\Models\PartnerProfile;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\CompletionConfirmed;
use App\Notifications\JobCompleted;
use App\Notifications\JobStarted;
use App\Notifications\OfferAccepted;
use App\Notifications\OfferReceived;
use App\Notifications\OfferRejected;
use App\Notifications\RequestAssigned;
use App\Notifications\RequestCancelled;
use App\Notifications\ReviewReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Memastikan setiap aksi penting mengirim notifikasi ke pengguna yang tepat.
 */
class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function assertNotified(User $user, string $notificationClass): void
    {
        $this->assertTrue(
            $user->notifications()->where('type', $notificationClass)->exists(),
            "Pengguna {$user->email} tidak menerima notifikasi {$notificationClass}.",
        );
    }

    private function partnerWithService(Service $service): User
    {
        $partner = User::factory()->partner()->create();
        $partner->partnerProfileOrCreate()->services()->attach($service);

        return $partner;
    }

    public function test_the_customer_is_notified_when_a_partner_sends_an_offer(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $serviceRequest = ServiceRequest::factory()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(PartnerRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->set('price', '175000')
            ->call('sendOffer')
            ->assertHasNoErrors();

        $this->assertNotified($serviceRequest->customer, OfferReceived::class);
    }

    public function test_accepting_an_offer_notifies_the_winning_and_losing_partners(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();
        $winnerProfile = PartnerProfile::factory()->create();
        $loserProfile = PartnerProfile::factory()->create();

        $winningOffer = Offer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'partner_profile_id' => $winnerProfile->id,
        ]);

        Offer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'partner_profile_id' => $loserProfile->id,
        ]);

        Livewire::actingAs($serviceRequest->customer)
            ->test(CustomerRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('acceptOffer', $winningOffer->id);

        $this->assertNotified($winnerProfile->user, OfferAccepted::class);
        $this->assertNotified($loserProfile->user, OfferRejected::class);
    }

    public function test_the_customer_is_notified_when_a_partner_takes_a_direct_job(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $serviceRequest = ServiceRequest::factory()->direct()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(PartnerRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('takeJobNow');

        $this->assertNotified($serviceRequest->customer, RequestAssigned::class);
    }

    public function test_the_customer_is_notified_when_the_job_starts_and_finishes(): void
    {
        $partnerProfile = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->scheduled($partnerProfile)->create();

        $job = fn () => Livewire::actingAs($partnerProfile->user)
            ->test(PartnerJobShow::class, ['serviceRequest' => $serviceRequest]);

        $job()->call('start')->assertHasNoErrors();
        $this->assertNotified($serviceRequest->customer, JobStarted::class);

        $serviceRequest->refresh();

        $job()->call('complete')->assertHasNoErrors();
        $this->assertNotified($serviceRequest->customer, JobCompleted::class);
    }

    public function test_the_partner_is_notified_when_the_customer_confirms_completion(): void
    {
        $partnerProfile = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->inProgress($partnerProfile)->create();

        Livewire::actingAs($serviceRequest->customer)
            ->test(CustomerRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('confirmCompletion');

        $this->assertNotified($partnerProfile->user, CompletionConfirmed::class);
    }

    public function test_the_partner_is_notified_when_the_customer_cancels_an_assigned_request(): void
    {
        $partnerProfile = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partnerProfile)->create();

        Livewire::actingAs($serviceRequest->customer)
            ->test(CustomerRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('cancel');

        $this->assertNotified($partnerProfile->user, RequestCancelled::class);
    }

    public function test_the_customer_is_notified_when_the_partner_cancels_a_job(): void
    {
        $partnerProfile = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->scheduled($partnerProfile)->create();

        Livewire::actingAs($partnerProfile->user)
            ->test(PartnerJobShow::class, ['serviceRequest' => $serviceRequest])
            ->call('cancel');

        $this->assertNotified($serviceRequest->customer, RequestCancelled::class);
    }

    public function test_the_partner_is_notified_when_a_review_is_submitted(): void
    {
        $partnerProfile = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed($partnerProfile)->create();

        Livewire::actingAs($serviceRequest->customer)
            ->test(CustomerRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->set('rating', '5')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertNotified($partnerProfile->user, ReviewReceived::class);
    }
}
