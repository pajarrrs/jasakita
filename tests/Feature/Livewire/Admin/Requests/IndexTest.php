<?php

namespace Tests\Feature\Livewire\Admin\Requests;

use App\Enums\RequestStatus;
use App\Livewire\Admin\Requests\Index;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_requests_from_all_customers(): void
    {
        ServiceRequest::factory()->create(['title' => 'Servis AC kamar utama']);
        ServiceRequest::factory()->completed()->create([
            'title' => 'Bersihkan kulkas dua pintu',
            'customer_id' => User::factory()->create(['name' => 'Rina Pelanggan'])->id,
        ]);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->assertSee('Servis AC kamar utama')
            ->assertSee('Bersihkan kulkas dua pintu')
            ->assertSee('Rina Pelanggan');
    }

    public function test_filters_by_status(): void
    {
        ServiceRequest::factory()->create(['title' => 'Masih menunggu penawaran']);
        ServiceRequest::factory()->completed()->create(['title' => 'Sudah selesai dikerjakan']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->set('status', RequestStatus::Completed->value)
            ->assertSee('Sudah selesai dikerjakan')
            ->assertDontSee('Masih menunggu penawaran');
    }

    public function test_searches_by_customer_name(): void
    {
        ServiceRequest::factory()->create([
            'title' => 'Servis AC kamar utama',
            'customer_id' => User::factory()->create(['name' => 'Rina Pelanggan'])->id,
        ]);
        ServiceRequest::factory()->create(['title' => 'Pasang keramik dapur']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->set('search', 'Rina')
            ->assertSee('Servis AC kamar utama')
            ->assertDontSee('Pasang keramik dapur');
    }

    public function test_shows_an_empty_state_without_requests(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Index::class)
            ->assertSee('Belum ada request');
    }
}
