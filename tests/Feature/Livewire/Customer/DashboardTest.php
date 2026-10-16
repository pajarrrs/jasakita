<?php

namespace Tests\Feature\Livewire\Customer;

use App\Livewire\Customer\Dashboard;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_categories_with_their_active_service_counts(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Servis Genset']);
        Service::factory()->count(2)->for($category, 'category')->create();
        Service::factory()->for($category, 'category')->inactive()->create();

        $inactiveCategory = ServiceCategory::factory()->inactive()->create(['name' => 'Kategori Nonaktif']);

        $this->actingAs(User::factory()->create())
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Servis Genset')
            ->assertSee('2 layanan')
            ->assertDontSee($inactiveCategory->name);
    }

    public function test_renders_empty_state_when_no_category_exists(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Dashboard::class)
            ->assertSee('Belum ada kategori jasa');
    }
}
