<?php

namespace Tests\Feature\Livewire\Partner\Requests;

use App\Enums\RequestStatus;
use App\Livewire\Partner\Requests\Index;
use App\Models\Offer;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $pivot
     */
    private function partnerWithService(Service $service, array $pivot = []): User
    {
        $partner = User::factory()->partner()->create();
        $partner->partnerProfileOrCreate()->services()->attach($service, $pivot);

        return $partner;
    }

    public function test_lists_open_requests_for_offered_services_only(): void
    {
        $service = Service::factory()->create();
        $otherService = Service::factory()->create();
        $partner = $this->partnerWithService($service);

        ServiceRequest::factory()->create([
            'service_id' => $service->id,
            'title' => 'Servis AC kamar utama',
        ]);
        ServiceRequest::factory()->create([
            'service_id' => $otherService->id,
            'title' => 'Jasa di luar keahlianku',
        ]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->assertSee('Servis AC kamar utama')
            ->assertDontSee('Jasa di luar keahlianku');
    }

    public function test_hides_requests_the_partner_already_responded_to(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);

        $serviceRequest = ServiceRequest::factory()->create([
            'service_id' => $service->id,
            'title' => 'Sudah kukirimi penawaran',
        ]);

        Offer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'partner_profile_id' => $partner->partnerProfileOrCreate()->id,
        ]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->assertDontSee('Sudah kukirimi penawaran');
    }

    public function test_taking_a_direct_job_assigns_the_partner_with_their_own_price(): void
    {
        $service = Service::factory()->create(['base_price' => 150000]);
        $partner = $this->partnerWithService($service, ['custom_price' => 185000]);

        $serviceRequest = ServiceRequest::factory()->direct()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->call('takeJob', $serviceRequest->id)
            ->assertRedirect(route('partner.jobs.show', $serviceRequest));

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Assigned, $serviceRequest->status);
        $this->assertSame($partner->partnerProfileOrCreate()->id, $serviceRequest->partner_profile_id);
        $this->assertSame('185000.00', $serviceRequest->agreed_price);
        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $serviceRequest->id,
            'status' => RequestStatus::Assigned->value,
        ]);
    }

    public function test_a_quote_request_cannot_be_taken_directly(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $serviceRequest = ServiceRequest::factory()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->call('takeJob', $serviceRequest->id);

        $serviceRequest->refresh();

        $this->assertNull($serviceRequest->partner_profile_id);
        $this->assertSame(RequestStatus::Open, $serviceRequest->status);
    }

    public function test_a_partner_that_is_not_accepting_jobs_cannot_take_a_direct_job(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $partner->partnerProfileOrCreate()->update(['is_available' => false]);

        $serviceRequest = ServiceRequest::factory()->direct()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->call('takeJob', $serviceRequest->id);

        $this->assertNull($serviceRequest->refresh()->partner_profile_id);
    }

    public function test_a_direct_job_can_only_be_taken_once(): void
    {
        $service = Service::factory()->create();
        $first = $this->partnerWithService($service);
        $second = $this->partnerWithService($service);

        $serviceRequest = ServiceRequest::factory()->direct()->create(['service_id' => $service->id]);

        Livewire::actingAs($first)->test(Index::class)->call('takeJob', $serviceRequest->id);
        Livewire::actingAs($second)->test(Index::class)->call('takeJob', $serviceRequest->id);

        $serviceRequest->refresh();

        $this->assertSame($first->partnerProfileOrCreate()->id, $serviceRequest->partner_profile_id);
    }

    public function test_a_partner_cannot_take_a_job_for_a_service_they_do_not_offer(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService(Service::factory()->create());

        $serviceRequest = ServiceRequest::factory()->direct()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->call('takeJob', $serviceRequest->id)
            ->assertStatus(403);
    }
}
