<?php

namespace App\Livewire\Admin\Categories;

use App\Models\ServiceCategory;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    /**
     * Ikon Heroicons yang boleh dipakai kategori.
     *
     * @var array<int, string>
     */
    public const ICONS = [
        'wrench-screwdriver',
        'sparkles',
        'scissors',
        'camera',
        'document-text',
        'cog-6-tooth',
        'home',
        'key',
        'paint-brush',
        'bolt',
        'truck',
        'shield-check',
        'fire',
        'trophy',
        'clock',
    ];

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $icon = 'wrench-screwdriver';

    public bool $isActive = true;

    public int $sortOrder = 0;

    public function create(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function edit(ServiceCategory $category): void
    {
        $this->resetForm();

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->icon = $category->icon ?? 'wrench-screwdriver';
        $this->isActive = $category->is_active;
        $this->sortOrder = $category->sort_order;

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['required', 'string', Rule::in(self::ICONS)],
            'isActive' => ['required', 'boolean'],
            'sortOrder' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $attributes = [
            'name' => $validated['name'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'icon' => $validated['icon'],
            'is_active' => $validated['isActive'],
            'sort_order' => $validated['sortOrder'],
        ];

        if ($this->editingId !== null) {
            ServiceCategory::findOrFail($this->editingId)->update($attributes);

            Flux::toast(text: 'Kategori diperbarui.', variant: 'success');
        } else {
            ServiceCategory::create([
                ...$attributes,
                'slug' => $this->uniqueSlug($validated['name']),
            ]);

            Flux::toast(text: 'Kategori ditambahkan.', variant: 'success');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(ServiceCategory $category): void
    {
        if ($category->services()->exists()) {
            Flux::toast(
                text: 'Kategori ini masih punya jasa. Pindahkan atau hapus jasanya dulu.',
                variant: 'danger',
            );

            return;
        }

        $category->delete();

        Flux::toast(text: 'Kategori dihapus.', variant: 'success');
    }

    /**
     * @return LengthAwarePaginator<int, ServiceCategory>
     */
    #[Computed]
    public function categories(): LengthAwarePaginator
    {
        return ServiceCategory::query()
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.admin.categories.index', [
            'icons' => self::ICONS,
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->icon = 'wrench-screwdriver';
        $this->isActive = true;
        $this->sortOrder = 0;

        $this->resetValidation();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (ServiceCategory::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
