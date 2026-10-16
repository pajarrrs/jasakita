<?php

namespace Tests\Feature\Livewire\Partner\Services;

use App\Livewire\Partner\Services\Index;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private function partner(): User
    {
        return User::factory()->partner()->create();
    }

    public function test_lists_active_services_grouped_by_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Pendingin']);
        Service::factory()->for($category, 'category')->create(['name' => 'Servis AC Portabel']);

        Livewire::actingAs($this->partner())
            ->test(Index::class)
            ->assertSee('Pendingin')
            ->assertSee('Servis AC Portabel');
    }

    public function test_shows_empty_state_when_no_active_service_exists(): void
    {
        Service::factory()->inactive()->create(['name' => 'Jasa Belum Tayang']);

        Livewire::actingAs($this->partner())
            ->test(Index::class)
            ->assertSee('Belum ada jasa yang bisa dipilih')
            ->assertDontSee('Jasa Belum Tayang');
    }

    public function test_offers_a_service_with_a_custom_price_and_note(): void
    {
        $service = Service::factory()->create(['name' => 'Servis AC Portabel']);
        $partner = $this->partner();

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->set("selection.{$service->id}.offered", true)
            ->set("selection.{$service->id}.customPrice", '175000')
            ->set("selection.{$service->id}.note", 'Termasuk isi freon')
            ->call('save')
            ->assertHasNoErrors();

        $profile = $partner->partnerProfile()->firstOrFail()->fresh();

        $this->assertDatabaseHas('partner_services', [
            'partner_profile_id' => $profile->id,
            'service_id' => $service->id,
            'note' => 'Termasuk isi freon',
        ]);
        $this->assertSame('175000.00', $profile->effectivePriceFor($service));
    }

    public function test_falls_back_to_the_base_price_without_a_custom_price(): void
    {
        $service = Service::factory()->create(['base_price' => 150000]);
        $partner = $this->partner();

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->set("selection.{$service->id}.offered", true)
            ->call('save')
            ->assertHasNoErrors();

        $profile = $partner->partnerProfile()->firstOrFail()->fresh();

        $this->assertSame('150000.00', $profile->effectivePriceFor($service));
    }

    public function test_removes_a_service_that_is_no_longer_offered(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partner();
        $partner->partnerProfileOrCreate()->services()->attach($service, ['custom_price' => 100000]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->assertSet("selection.{$service->id}.offered", true)
            ->set("selection.{$service->id}.offered", false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('partner_services', [
            'partner_profile_id' => $partner->partnerProfile()->firstOrFail()->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_rejects_a_negative_custom_price_for_an_offered_service(): void
    {
        $service = Service::factory()->create();
        $partner = $this->partner();

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->set("selection.{$service->id}.offered", true)
            ->set("selection.{$service->id}.customPrice", '-1')
            ->call('save')
            ->assertHasErrors(["selection.{$service->id}.customPrice" => 'min']);

        $this->assertDatabaseCount('partner_services', 0);
    }

    public function test_ignores_the_price_of_services_that_are_not_offered(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->partner())
            ->test(Index::class)
            ->set("selection.{$service->id}.offered", false)
            ->set("selection.{$service->id}.customPrice", 'bukan-angka')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('partner_services', 0);
    }

    public function test_keeps_offered_services_that_are_no_longer_active(): void
    {
        $partner = $this->partner();
        $service = Service::factory()->create(['is_active' => false]);
        $partner->partnerProfileOrCreate()->services()->attach($service, ['custom_price' => 90000]);

        Livewire::actingAs($partner)
            ->test(Index::class)
            ->assertSet('selection', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('partner_services', [
            'service_id' => $service->id,
        ]);
    }
}
