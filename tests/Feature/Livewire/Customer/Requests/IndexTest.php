<?php

namespace Tests\Feature\Livewire\Customer\Requests;

use App\Enums\RequestStatus;
use App\Livewire\Customer\Requests\Index;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_the_signed_in_customers_requests(): void
    {
        $customer = User::factory()->create();

        ServiceRequest::factory()->create([
            'customer_id' => $customer->id,
            'title' => 'Servis AC kamar utama',
        ]);
        ServiceRequest::factory()->create(['title' => 'Request milik pelanggan lain']);

        Livewire::actingAs($customer)
            ->test(Index::class)
            ->assertSee('Servis AC kamar utama')
            ->assertDontSee('Request milik pelanggan lain');
    }

    public function test_filters_requests_by_status(): void
    {
        $customer = User::factory()->create();

        ServiceRequest::factory()->create([
            'customer_id' => $customer->id,
            'title' => 'Masih menunggu penawaran',
            'status' => RequestStatus::Open,
        ]);
        ServiceRequest::factory()->completed()->create([
            'customer_id' => $customer->id,
            'title' => 'Sudah selesai dikerjakan',
        ]);

        Livewire::actingAs($customer)
            ->test(Index::class)
            ->set('status', RequestStatus::Completed->value)
            ->assertSee('Sudah selesai dikerjakan')
            ->assertDontSee('Masih menunggu penawaran');
    }

    public function test_shows_an_empty_state_without_requests(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Index::class)
            ->assertSee('Belum ada request');
    }
}
