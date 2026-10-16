<?php

namespace Tests\Feature\Livewire\Partner\Jobs;

use App\Enums\RequestStatus;
use App\Livewire\Partner\Jobs\Index;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_active_jobs_by_default(): void
    {
        $partner = User::factory()->partner()->create();
        $profile = $partner->partnerProfileOrCreate();

        ServiceRequest::factory()->scheduled($profile)->create(['title' => 'Job terjadwal besok']);
        ServiceRequest::factory()->completed($profile)->create(['title' => 'Job yang sudah selesai']);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->assertSee('Job terjadwal besok')
            ->assertDontSee('Job yang sudah selesai');
    }

    public function test_can_filter_completed_jobs(): void
    {
        $partner = User::factory()->partner()->create();
        $profile = $partner->partnerProfileOrCreate();

        ServiceRequest::factory()->scheduled($profile)->create(['title' => 'Job terjadwal besok']);
        ServiceRequest::factory()->completed($profile)->create(['title' => 'Job yang sudah selesai']);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->set('status', RequestStatus::Completed->value)
            ->assertSee('Job yang sudah selesai')
            ->assertDontSee('Job terjadwal besok');
    }

    public function test_does_not_show_jobs_of_other_partners(): void
    {
        $partner = User::factory()->partner()->create();
        $otherPartner = User::factory()->partner()->create();

        ServiceRequest::factory()->scheduled($partner->partnerProfileOrCreate())
            ->create(['title' => 'Job milikku']);
        ServiceRequest::factory()->scheduled($otherPartner->partnerProfileOrCreate())
            ->create(['title' => 'Job mitra lain']);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->assertSee('Job milikku')
            ->assertDontSee('Job mitra lain');
    }

    public function test_shows_an_empty_state_without_jobs(): void
    {
        Livewire::actingAs(User::factory()->partner()->create())
            ->test(Index::class)
            ->assertSee('Belum ada job');
    }
}
