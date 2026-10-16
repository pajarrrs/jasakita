<?php

namespace Tests\Feature\Livewire\Customer\Categories;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_active_services_of_the_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Servis Genset']);
        Service::factory()->for($category, 'category')->create(['name' => 'Servis Genset 5000 Watt']);
        Service::factory()->for($category, 'category')->inactive()->create(['name' => 'Jasa Belum Tayang']);

        $this->actingAs(User::factory()->create())
            ->get(route('customer.categories.show', $category))
            ->assertOk()
            ->assertSee('Servis Genset')
            ->assertSee('Servis Genset 5000 Watt')
            ->assertDontSee('Jasa Belum Tayang');
    }

    public function test_does_not_show_services_from_another_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Jahit']);
        $otherCategory = ServiceCategory::factory()->create(['name' => 'Pendingin']);

        Service::factory()->for($otherCategory, 'category')->create(['name' => 'Servis Kulkas']);

        $this->actingAs(User::factory()->create())
            ->get(route('customer.categories.show', $category))
            ->assertOk()
            ->assertDontSee('Servis Kulkas');
    }

    public function test_returns_not_found_for_an_inactive_category(): void
    {
        $category = ServiceCategory::factory()->inactive()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('customer.categories.show', $category))
            ->assertNotFound();
    }
}
