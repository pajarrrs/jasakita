<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_category_service_user_and_partner_counts(): void
    {
        $admin = User::factory()->admin()->create();

        User::factory()->count(2)->create();
        User::factory()->partner()->create();

        $categories = ServiceCategory::factory()->count(2)->create();
        Service::factory()->count(3)->recycle($categories)->create();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertViewHas('categoryCount', 2)
            ->assertViewHas('serviceCount', 3)
            ->assertViewHas('userCount', 4)
            ->assertViewHas('partnerCount', 1);
    }
}
