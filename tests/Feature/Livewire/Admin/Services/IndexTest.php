<?php

namespace Tests\Feature\Livewire\Admin\Services;

use App\Livewire\Admin\Services\Index;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_lists_services_with_category_and_price(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Pendingin']);
        Service::factory()->for($category, 'category')->create([
            'name' => 'Servis AC Portabel',
            'base_price' => 150000,
        ]);

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->assertSee('Servis AC Portabel')
            ->assertSee('Pendingin')
            ->assertSee('Rp 150.000', escape: false);
    }

    public function test_creates_service_with_generated_slug(): void
    {
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis AC')
            ->set('categoryId', (string) $category->id)
            ->set('basePrice', '175000')
            ->set('priceUnit', 'per_job')
            ->set('durationEstimate', '1-2 jam')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('services', [
            'name' => 'Servis AC',
            'slug' => 'servis-ac',
            'service_category_id' => $category->id,
            'price_unit' => 'per_job',
            'duration_estimate' => '1-2 jam',
            'is_active' => true,
        ]);
    }

    public function test_updates_service_and_keeps_the_original_slug(): void
    {
        $service = Service::factory()->create(['name' => 'Servis AC', 'slug' => 'servis-ac']);
        $otherCategory = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('edit', $service->id)
            ->set('name', 'Servis AC Split')
            ->set('categoryId', (string) $otherCategory->id)
            ->call('save')
            ->assertHasNoErrors();

        $service->refresh();

        $this->assertSame('Servis AC Split', $service->name);
        $this->assertSame('servis-ac', $service->slug);
        $this->assertSame($otherCategory->id, $service->service_category_id);
    }

    public function test_requires_an_existing_category(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis AC')
            ->set('categoryId', '99999')
            ->call('save')
            ->assertHasErrors(['categoryId' => 'exists']);

        $this->assertDatabaseCount('services', 0);
    }

    public function test_rejects_a_price_unit_outside_the_enum(): void
    {
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis AC')
            ->set('categoryId', (string) $category->id)
            ->set('priceUnit', 'per_bulan')
            ->call('save')
            ->assertHasErrors(['priceUnit']);

        $this->assertDatabaseCount('services', 0);
    }

    public function test_rejects_a_negative_base_price(): void
    {
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis AC')
            ->set('categoryId', (string) $category->id)
            ->set('basePrice', '-1')
            ->call('save')
            ->assertHasErrors(['basePrice' => 'min']);
    }

    public function test_filters_services_by_search_term_and_category(): void
    {
        $pendingin = ServiceCategory::factory()->create(['name' => 'Pendingin']);
        $jahit = ServiceCategory::factory()->create(['name' => 'Jahit']);

        Service::factory()->for($pendingin, 'category')->create(['name' => 'Servis AC Portabel']);
        Service::factory()->for($pendingin, 'category')->create(['name' => 'Servis Kulkas Dua Pintu']);
        Service::factory()->for($jahit, 'category')->create(['name' => 'Permak Celana Jeans']);

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->set('search', 'servis')
            ->assertSee('Servis AC Portabel')
            ->assertSee('Servis Kulkas Dua Pintu')
            ->assertDontSee('Permak Celana Jeans')
            ->set('search', '')
            ->set('categoryFilter', (string) $jahit->id)
            ->assertSee('Permak Celana Jeans')
            ->assertDontSee('Servis AC Portabel')
            ->assertDontSee('Servis Kulkas Dua Pintu');
    }

    public function test_resets_to_the_first_page_when_the_search_term_changes(): void
    {
        $category = ServiceCategory::factory()->create();

        foreach (range('A', 'L') as $letter) {
            Service::factory()->for($category, 'category')->create(['name' => "Jasa {$letter}"]);
        }

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('setPage', 2)
            ->assertSee('Jasa L')
            ->assertDontSee('Jasa A')
            ->set('search', 'Jasa L')
            ->assertSee('Jasa L')
            ->assertDontSee('Jasa A');
    }

    public function test_deletes_service(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('delete', $service->id);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
