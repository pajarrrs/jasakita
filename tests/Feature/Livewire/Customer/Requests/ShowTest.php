<?php

namespace Tests\Feature\Livewire\Customer\Requests;

use App\Enums\OfferStatus;
use App\Enums\RequestStatus;
use App\Livewire\Customer\Requests\Show;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_sees_request_details_and_incoming_offers(): void
    {
        $customer = User::factory()->create();
        $offer = Offer::factory()->create([
            'price' => 175000,
            'message' => 'Termasuk isi freon dan cuci.',
        ]);
        $offer->serviceRequest->update([
            'customer_id' => $customer->id,
            'title' => 'Servis AC kamar utama',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.requests.show', $offer->serviceRequest))
            ->assertOk()
            ->assertSee('Servis AC kamar utama')
            ->assertSee('Rp 175.000')
            ->assertSee('Termasuk isi freon dan cuci.')
            ->assertSee($offer->partner->user->name);
    }

    public function test_a_customer_cannot_open_someone_elses_request(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('customer.requests.show', $serviceRequest))
            ->assertForbidden();
    }

    public function test_accepting_an_offer_assigns_the_partner_and_rejects_other_offers(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['customer_id' => $customer->id]);

        $chosen = Offer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'price' => 200000,
        ]);
        $other = Offer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'price' => 250000,
        ]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('acceptOffer', $chosen->id)
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Assigned, $serviceRequest->status);
        $this->assertSame($chosen->partner_profile_id, $serviceRequest->partner_profile_id);
        $this->assertSame('200000.00', $serviceRequest->agreed_price);
        $this->assertSame(OfferStatus::Accepted, $chosen->refresh()->status);
        $this->assertSame(OfferStatus::Rejected, $other->refresh()->status);

        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $serviceRequest->id,
            'status' => RequestStatus::Assigned->value,
        ]);
    }

    public function test_an_offer_cannot_be_accepted_after_a_partner_is_assigned(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned()->create(['customer_id' => $customer->id]);
        $offer = Offer::factory()->create(['service_request_id' => $serviceRequest->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('acceptOffer', $offer->id);

        $this->assertSame(OfferStatus::Pending, $offer->refresh()->status);
        $this->assertNotSame($offer->partner_profile_id, $serviceRequest->refresh()->partner_profile_id);
    }

    public function test_confirming_a_schedule_moves_the_request_to_scheduled(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned()->create(['customer_id' => $customer->id]);
        $schedule = now()->addDays(3)->setTime(10, 0);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('scheduledAt', $schedule->format('Y-m-d\TH:i'))
            ->call('confirmSchedule')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Scheduled, $serviceRequest->status);
        $this->assertSame($schedule->format('Y-m-d H:i'), $serviceRequest->scheduled_at->format('Y-m-d H:i'));
    }

    public function test_the_customer_can_confirm_completion_while_the_job_is_in_progress(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->inProgress()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('confirmCompletion')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Completed, $serviceRequest->status);
        $this->assertNotNull($serviceRequest->completed_at);
    }

    public function test_cancelling_a_request_withdraws_pending_offers(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['customer_id' => $customer->id]);
        $offer = Offer::factory()->create(['service_request_id' => $serviceRequest->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('cancellationReason', 'Rencana berubah.')
            ->call('cancel')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Cancelled, $serviceRequest->status);
        $this->assertSame('Rencana berubah.', $serviceRequest->cancellation_reason);
        $this->assertSame(OfferStatus::Withdrawn, $offer->refresh()->status);
    }

    public function test_a_completed_request_cannot_be_cancelled(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('cancel');

        $this->assertSame(RequestStatus::Completed, $serviceRequest->refresh()->status);
        $this->assertDatabaseMissing('request_status_histories', [
            'service_request_id' => $serviceRequest->id,
            'status' => RequestStatus::Cancelled->value,
        ]);
    }
}
