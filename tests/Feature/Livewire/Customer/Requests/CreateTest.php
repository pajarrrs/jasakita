<?php

namespace Tests\Feature\Livewire\Customer\Requests;

use App\Enums\RequestMode;
use App\Enums\RequestStatus;
use App\Livewire\Customer\Requests\Create;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_quote_request_from_the_service_page(): void
    {
        $service = Service::factory()->create(['name' => 'Servis AC Portabel']);
        $customer = User::factory()->create();

        Livewire::actingAs($customer)
            ->test(Create::class, ['service' => $service])
            ->assertSet('mode', 'quote')
            ->assertSet('title', 'Servis AC Portabel')
            ->set('title', 'Servis AC kamar tidur')
            ->set('description', 'AC tidak dingin sejak seminggu, unit indoor 1 PK.')
            ->set('address', 'Jl. Kenanga No. 12')
            ->set('city', 'Bandung')
            ->set('scheduledAt', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->call('createRequest')
            ->assertHasNoErrors();

        $serviceRequest = ServiceRequest::sole();

        $this->assertSame($customer->id, $serviceRequest->customer_id);
        $this->assertSame($service->id, $serviceRequest->service_id);
        $this->assertSame(RequestMode::Quote, $serviceRequest->mode);
        $this->assertSame(RequestStatus::Open, $serviceRequest->status);
        $this->assertSame('Jl. Kenanga No. 12', $serviceRequest->address);
        $this->assertSame('Bandung', $serviceRequest->city);

        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $serviceRequest->id,
            'status' => RequestStatus::Open->value,
            'changed_by' => $customer->id,
        ]);
    }

    public function test_reads_the_direct_order_mode_from_the_query_string(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->withQueryParams(['mode' => 'direct'])
            ->test(Create::class, ['service' => $service])
            ->assertSet('mode', 'direct')
            ->set('description', 'Butuh cepat hari ini, mohon diambil mitra terdekat.')
            ->set('address', 'Jl. Melati No. 4')
            ->call('createRequest')
            ->assertHasNoErrors();

        $this->assertSame(RequestMode::Direct, ServiceRequest::sole()->mode);
    }

    public function test_requires_a_description_of_at_least_ten_characters(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(Create::class, ['service' => $service])
            ->set('description', 'pendek')
            ->set('address', 'Jl. Melati No. 4')
            ->call('createRequest')
            ->assertHasErrors(['description' => 'min']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_requires_a_title_and_address(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(Create::class, ['service' => $service])
            ->set('title', '')
            ->set('description', 'AC tidak dingin sejak seminggu.')
            ->set('address', '')
            ->call('createRequest')
            ->assertHasErrors(['title' => 'required', 'address' => 'required']);
    }

    public function test_rejects_a_schedule_in_the_past(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(Create::class, ['service' => $service])
            ->set('description', 'AC tidak dingin sejak seminggu.')
            ->set('address', 'Jl. Melati No. 4')
            ->set('scheduledAt', now()->subDay()->format('Y-m-d\TH:i'))
            ->call('createRequest')
            ->assertHasErrors(['scheduledAt' => 'after']);
    }

    public function test_an_inactive_service_cannot_be_requested(): void
    {
        $service = Service::factory()->inactive()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(Create::class, ['service' => $service])
            ->assertStatus(404);
    }
}
