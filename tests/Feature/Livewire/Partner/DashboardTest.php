<?php

namespace Tests\Feature\Livewire\Partner;

use App\Livewire\Partner\Dashboard;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompts_the_partner_to_complete_their_profile_and_services(): void
    {
        $partner = User::factory()->partner()->create();

        Livewire::actingAs($partner)
            ->test(Dashboard::class)
            ->assertSee('Lengkapi profil mitra')
            ->assertSee('Pilih layanan dulu')
            ->assertViewHas('offeredServicesCount', 0);
    }

    public function test_counts_offered_services_and_hides_the_setup_prompts(): void
    {
        $partner = User::factory()->partner()->create();
        $profile = $partner->partnerProfileOrCreate();
        $profile->update(['bio' => 'Spesialis AC.', 'city' => 'Bandung']);

        $services = Service::factory()->count(2)->create();
        $profile->services()->attach($services->pluck('id'));

        Livewire::actingAs($partner)
            ->test(Dashboard::class)
            ->assertViewHas('offeredServicesCount', 2)
            ->assertDontSee('Lengkapi profil mitra')
            ->assertDontSee('Pilih layanan dulu');
    }

    public function test_tells_the_partner_when_they_are_not_accepting_jobs(): void
    {
        $partner = User::factory()->partner()->create();
        $partner->partnerProfileOrCreate()->update([
            'bio' => 'Spesialis AC.',
            'city' => 'Bandung',
            'is_available' => false,
        ]);

        Livewire::actingAs($partner)
            ->test(Dashboard::class)
            ->assertSee('Kamu sedang tidak menerima job baru');
    }
}
