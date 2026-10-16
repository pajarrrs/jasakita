<?php

namespace Tests\Feature\Livewire\Admin\Categories;

use App\Livewire\Admin\Categories\Index;
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

    public function test_lists_categories_with_their_service_counts(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Servis Genset']);
        Service::factory()->count(2)->for($category, 'category')->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->assertSee('Servis Genset')
            ->assertSee('2');
    }

    public function test_creates_category_with_generated_slug(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis Genset')
            ->set('description', 'Perawatan genset rumah dan usaha.')
            ->set('icon', 'cog-6-tooth')
            ->set('sortOrder', 3)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('service_categories', [
            'name' => 'Servis Genset',
            'slug' => 'servis-genset',
            'icon' => 'cog-6-tooth',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    public function test_generates_unique_slug_when_the_name_is_already_taken(): void
    {
        ServiceCategory::factory()->create(['slug' => 'servis-ac']);

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis AC')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_categories', ['slug' => 'servis-ac-2']);
    }

    public function test_updates_category_and_keeps_the_original_slug(): void
    {
        $category = ServiceCategory::factory()->create([
            'name' => 'Servis AC',
            'slug' => 'servis-ac',
        ]);

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('edit', $category->id)
            ->set('name', 'Servis AC & Kulkas')
            ->call('save')
            ->assertHasNoErrors();

        $category->refresh();

        $this->assertSame('Servis AC & Kulkas', $category->name);
        $this->assertSame('servis-ac', $category->slug);
    }

    public function test_rejects_an_icon_outside_the_allowed_list(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', 'Servis Genset')
            ->set('icon', 'not-a-real-icon')
            ->call('save')
            ->assertHasErrors(['icon']);

        $this->assertDatabaseCount('service_categories', 0);
    }

    public function test_requires_a_name(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('service_categories', 0);
    }

    public function test_paginates_categories_and_shows_the_result_summary(): void
    {
        foreach (range('A', 'L') as $position => $letter) {
            ServiceCategory::factory()->create([
                'name' => "Kategori {$letter}",
                'sort_order' => $position + 1,
            ]);
        }

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->assertSee('Menampilkan 1 sampai 10 dari 12 hasil')
            ->assertSee('Kategori A')
            ->assertDontSee('Kategori L')
            ->call('setPage', 2)
            ->assertSee('Menampilkan 11 sampai 12 dari 12 hasil')
            ->assertSee('Kategori L')
            ->assertDontSee('Kategori A');
    }

    public function test_refuses_to_delete_a_category_that_still_has_services(): void
    {
        $category = ServiceCategory::factory()->create();
        Service::factory()->for($category, 'category')->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('delete', $category->id);

        $this->assertDatabaseHas('service_categories', ['id' => $category->id]);
        $this->assertDatabaseCount('services', 1);
    }

    public function test_deletes_a_category_without_services(): void
    {
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('delete', $category->id);

        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
    }
}
