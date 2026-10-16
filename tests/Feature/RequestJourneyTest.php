<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Livewire\Customer\Requests\Create as CustomerRequestsCreate;
use App\Livewire\Customer\Requests\Show as CustomerRequestsShow;
use App\Livewire\Partner\Jobs\Show as PartnerJobsShow;
use App\Livewire\Partner\Requests\Show as PartnerRequestsShow;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RequestJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_request_is_fulfilled_end_to_end(): void
    {
        $service = Service::factory()->create([
            'name' => 'Servis AC Portabel',
            'base_price' => 150000,
        ]);

        $customer = User::factory()->create();
        $partner = User::factory()->partner()->create();
        $profile = $partner->partnerProfileOrCreate();
        $profile->services()->attach($service, ['custom_price' => 175000]);

        Livewire::actingAs($customer)
            ->withQueryParams(['mode' => 'quote'])
            ->test(CustomerRequestsCreate::class, ['service' => $service])
            ->set('description', 'AC tidak dingin sejak seminggu, unit indoor 1 PK.')
            ->set('address', 'Jl. Kenanga No. 12')
            ->set('city', 'Bandung')
            ->call('createRequest')
            ->assertHasNoErrors();

        $serviceRequest = ServiceRequest::sole();

        $this->assertSame(RequestStatus::Open, $serviceRequest->status);

        Livewire::actingAs($partner)
            ->test(PartnerRequestsShow::class, ['serviceRequest' => $serviceRequest])
            ->set('price', '175000')
            ->set('durationEstimate', '1-2 jam')
            ->call('sendOffer')
            ->assertHasNoErrors();

        $offer = $serviceRequest->offers()->sole();

        Livewire::actingAs($customer)
            ->test(CustomerRequestsShow::class, ['serviceRequest' => $serviceRequest])
            ->call('acceptOffer', $offer->id)
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Assigned, $serviceRequest->status);
        $this->assertSame($profile->id, $serviceRequest->partner_profile_id);
        $this->assertSame('175000.00', $serviceRequest->agreed_price);

        Livewire::actingAs($partner)
            ->test(PartnerJobsShow::class, ['serviceRequest' => $serviceRequest])
            ->set('scheduledAt', now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i'))
            ->call('confirmSchedule')
            ->assertHasNoErrors();

        $this->assertSame(RequestStatus::Scheduled, $serviceRequest->refresh()->status);

        Livewire::actingAs($partner)
            ->test(PartnerJobsShow::class, ['serviceRequest' => $serviceRequest])
            ->call('start')
            ->call('complete')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Completed, $serviceRequest->status);
        $this->assertNotNull($serviceRequest->completed_at);

        $this->assertSame(
            ['open', 'assigned', 'scheduled', 'in_progress', 'completed'],
            $serviceRequest->statusHistories()->pluck('status')->map->value->all(),
        );

        Livewire::actingAs($customer)
            ->test(CustomerRequestsShow::class, ['serviceRequest' => $serviceRequest])
            ->set('rating', '5')
            ->set('comment', 'AC dingin lagi dan lokasi dibersihkan setelah selesai.')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertTrue($serviceRequest->refresh()->isReviewed());
        $this->assertSame(5.0, $profile->fresh()->ratingAverage());
    }
}
