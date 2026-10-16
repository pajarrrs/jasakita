<?php

namespace Tests\Feature\Livewire\Customer\Categories;

use App\Livewire\Customer\Categories\Index;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_categories_with_their_active_service_counts(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Servis Genset']);
        Service::factory()->count(2)->for($category, 'category')->create();
        Service::factory()->for($category, 'category')->inactive()->create();

        ServiceCategory::factory()->inactive()->create(['name' => 'Kategori Nonaktif']);

        Livewire::actingAs(User::factory()->create())
            ->test(Index::class)
            ->assertSee('Servis Genset')
            ->assertSee('2 layanan')
            ->assertDontSee('Kategori Nonaktif');
    }

    public function test_search_returns_matching_active_services_only(): void
    {
        $category = ServiceCategory::factory()->create();

        Service::factory()->for($category, 'category')->create(['name' => 'Servis AC']);
        Service::factory()->for($category, 'category')->create(['name' => 'Permak Celana']);
        Service::factory()->for($category, 'category')->inactive()->create(['name' => 'Servis Kulkas']);

        Livewire::actingAs(User::factory()->create())
            ->test(Index::class)
            ->set('search', 'Servis')
            ->assertSee('Servis AC')
            ->assertDontSee('Permak Celana')
            ->assertDontSee('Servis Kulkas');
    }

    public function test_shows_empty_state_when_no_category_exists(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Index::class)
            ->assertSee('Belum ada kategori jasa');
    }

    public function test_a_customer_can_browse_the_seeded_catalog_from_end_to_end(): void
    {
        $this->seed();

        $customer = User::where('email', 'pelanggan@jasakita.test')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('customer.categories.index'))
            ->assertOk();

        $category = ServiceCategory::where('is_active', true)->orderBy('sort_order')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('customer.categories.show', $category))
            ->assertOk();

        $service = $category->services()->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)
            ->get(route('customer.services.show', $service))
            ->assertOk()
            ->assertSee($service->name);
    }
}
