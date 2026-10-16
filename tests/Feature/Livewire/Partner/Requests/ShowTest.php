<?php

namespace Tests\Feature\Livewire\Partner\Requests;

use App\Enums\OfferStatus;
use App\Livewire\Partner\Requests\Show;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
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

    public function test_the_offer_form_is_prefilled_with_the_partner_price(): void
    {
        $service = Service::factory()->create(['base_price' => 150000]);
        $partner = $this->partnerWithService($service, ['custom_price' => 185000]);
        $serviceRequest = ServiceRequest::factory()->create([
            'service_id' => $service->id,
            'title' => 'Servis AC kamar utama',
        ]);

        $this->actingAs($partner)
            ->get(route('partner.requests.show', $serviceRequest))
            ->assertOk()
            ->assertSee('Servis AC kamar utama')
            ->assertSee('185000');
    }

    public function test_sending_an_offer_creates_a_pending_offer(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $serviceRequest = ServiceRequest::factory()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('price', '175000')
            ->set('durationEstimate', '1-2 jam')
            ->set('message', 'Termasuk isi freon.')
            ->call('sendOffer')
            ->assertHasNoErrors();

        $offer = $serviceRequest->offers()->sole();

        $this->assertSame('175000.00', $offer->price);
        $this->assertSame(OfferStatus::Pending, $offer->status);
        $this->assertSame($partner->partnerProfileOrCreate()->id, $offer->partner_profile_id);
        $this->assertSame('Termasuk isi freon.', $offer->message);
    }

    public function test_sending_an_offer_twice_updates_the_previous_one(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $serviceRequest = ServiceRequest::factory()->create(['service_id' => $service->id]);

        $component = fn () => Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest]);

        $component()->set('price', '175000')->call('sendOffer')->assertHasNoErrors();
        $component()->set('price', '160000')->call('sendOffer')->assertHasNoErrors();

        $this->assertSame(1, $serviceRequest->offers()->count());
        $this->assertSame('160000.00', $serviceRequest->offers()->sole()->price);
    }

    public function test_an_unavailable_partner_cannot_send_an_offer(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $partner->partnerProfileOrCreate()->update(['is_available' => false]);

        $serviceRequest = ServiceRequest::factory()->create(['service_id' => $service->id]);

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('price', '175000')
            ->call('sendOffer');

        $this->assertSame(0, $serviceRequest->offers()->count());
    }

    public function test_a_request_that_is_no_longer_open_is_not_accessible(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partnerWithService($service);
        $serviceRequest = ServiceRequest::factory()->assigned()->create([
            'service_id' => $service->id,
        ]);

        $this->actingAs($partner)
            ->get(route('partner.requests.show', $serviceRequest))
            ->assertNotFound();
    }

    public function test_a_partner_cannot_open_a_request_for_a_service_they_do_not_offer(): void
    {
        $partner = $this->partnerWithService(Service::factory()->create());
        $serviceRequest = ServiceRequest::factory()->create([
            'service_id' => Service::factory()->create()->id,
        ]);

        $this->actingAs($partner)
            ->get(route('partner.requests.show', $serviceRequest))
            ->assertForbidden();
    }
}
