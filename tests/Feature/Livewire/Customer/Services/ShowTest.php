<?php

namespace Tests\Feature\Livewire\Customer\Services;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_service_details_with_price_and_category(): void
    {
        $service = Service::factory()->create([
            'name' => 'Servis AC',
            'base_price' => 150000,
            'duration_estimate' => '1-2 jam',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('customer.services.show', $service))
            ->assertOk()
            ->assertSee('Servis AC')
            ->assertSee('Rp 150.000')
            ->assertSee('1-2 jam')
            ->assertSee($service->category->name);
    }

    public function test_returns_not_found_for_an_inactive_service(): void
    {
        $service = Service::factory()->inactive()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('customer.services.show', $service))
            ->assertNotFound();
    }
}
