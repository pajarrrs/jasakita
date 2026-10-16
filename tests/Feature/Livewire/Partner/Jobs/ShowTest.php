<?php

namespace Tests\Feature\Livewire\Partner\Jobs;

use App\Enums\RequestStatus;
use App\Livewire\Partner\Jobs\Show;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_partner_sees_the_customer_contact_and_job_details(): void
    {
        $partner = User::factory()->partner()->create();
        $customer = User::factory()->create(['name' => 'Rina Pelanggan', 'phone' => '081200000001']);
        $serviceRequest = ServiceRequest::factory()->scheduled($partner->partnerProfileOrCreate())->create([
            'customer_id' => $customer->id,
            'title' => 'Servis AC kamar utama',
            'address' => 'Jl. Kenanga No. 12',
            'city' => 'Bandung',
        ]);

        $this->actingAs($partner)
            ->get(route('partner.jobs.show', $serviceRequest))
            ->assertOk()
            ->assertSee('Servis AC kamar utama')
            ->assertSee('Rina Pelanggan')
            ->assertSee('Jl. Kenanga No. 12')
            ->assertSee('081200000001');
    }

    public function test_the_partner_sees_the_customer_review_after_completion(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->completed($partner->partnerProfileOrCreate())->create();

        Review::factory()->forRequest($serviceRequest)->create([
            'rating' => 5,
            'comment' => 'Kerjanya cepat dan bersih.',
        ]);

        $this->actingAs($partner)
            ->get(route('partner.jobs.show', $serviceRequest))
            ->assertOk()
            ->assertSee('Kerjanya cepat dan bersih.');
    }

    public function test_a_partner_cannot_open_another_partners_job(): void
    {
        $serviceRequest = ServiceRequest::factory()->scheduled()->create();

        $this->actingAs(User::factory()->partner()->create())
            ->get(route('partner.jobs.show', $serviceRequest))
            ->assertForbidden();
    }

    public function test_confirming_a_schedule_moves_the_job_to_scheduled(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partner->partnerProfileOrCreate())->create();
        $schedule = now()->addDays(2)->setTime(13, 0);

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('scheduledAt', $schedule->format('Y-m-d\TH:i'))
            ->call('confirmSchedule')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Scheduled, $serviceRequest->status);
        $this->assertSame($schedule->format('Y-m-d H:i'), $serviceRequest->scheduled_at->format('Y-m-d H:i'));
    }

    public function test_starting_the_job_moves_it_to_in_progress(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->scheduled($partner->partnerProfileOrCreate())->create();

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('start')
            ->assertHasNoErrors();

        $this->assertSame(RequestStatus::InProgress, $serviceRequest->refresh()->status);
        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $serviceRequest->id,
            'status' => RequestStatus::InProgress->value,
        ]);
    }

    public function test_completing_the_job_stores_the_completion_time(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->inProgress($partner->partnerProfileOrCreate())->create();

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('complete')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Completed, $serviceRequest->status);
        $this->assertNotNull($serviceRequest->completed_at);
    }

    public function test_a_job_must_be_started_before_it_can_be_completed(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->scheduled($partner->partnerProfileOrCreate())->create();

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('complete');

        $this->assertSame(RequestStatus::Scheduled, $serviceRequest->refresh()->status);
        $this->assertNull($serviceRequest->completed_at);
    }

    public function test_cancelling_a_scheduled_job_records_the_reason(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->scheduled($partner->partnerProfileOrCreate())->create();

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('cancellationReason', 'Jadwal bertabrakan.')
            ->call('cancel')
            ->assertHasNoErrors();

        $serviceRequest->refresh();

        $this->assertSame(RequestStatus::Cancelled, $serviceRequest->status);
        $this->assertSame('Jadwal bertabrakan.', $serviceRequest->cancellation_reason);
    }

    public function test_an_in_progress_job_cannot_be_cancelled(): void
    {
        $partner = User::factory()->partner()->create();
        $serviceRequest = ServiceRequest::factory()->inProgress($partner->partnerProfileOrCreate())->create();

        Livewire::actingAs($partner)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('cancel');

        $this->assertSame(RequestStatus::InProgress, $serviceRequest->refresh()->status);
    }
}
