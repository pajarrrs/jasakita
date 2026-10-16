<?php

namespace Tests\Feature\Livewire\Admin\Users;

use App\Livewire\Admin\Users\Index;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_users_with_their_role_and_activity(): void
    {
        $customer = User::factory()->create(['name' => 'Rina Pelanggan']);
        User::factory()->partner()->create(['name' => 'Budi Mitra']);
        User::factory()->admin()->create(['name' => 'Admin Utama']);

        ServiceRequest::factory()->count(2)->create(['customer_id' => $customer->id]);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->assertSee('Rina Pelanggan')
            ->assertSee('Budi Mitra')
            ->assertSee('Admin Utama')
            ->assertSee('2 request');
    }

    public function test_filters_by_role(): void
    {
        User::factory()->create(['name' => 'Rina Pelanggan']);
        User::factory()->partner()->create(['name' => 'Budi Mitra']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->set('role', 'partner')
            ->assertSee('Budi Mitra')
            ->assertDontSee('Rina Pelanggan');
    }

    public function test_searches_by_name_or_email(): void
    {
        User::factory()->create(['name' => 'Rina Pelanggan', 'email' => 'rina@example.com']);
        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->set('search', 'rina@example.com')
            ->assertSee('Rina Pelanggan')
            ->assertDontSee('Budi Santoso');
    }

    public function test_shows_an_empty_state_without_matching_users(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->set('search', 'tidakadaorang')
            ->assertSee('Tidak ada pengguna');
    }
}
