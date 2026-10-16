<?php

namespace App\Livewire\Admin\Services;

use App\Enums\PriceUnit;
use App\Models\Service;
use App\Models\ServiceCategory;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $categoryId = '';

    public string $description = '';

    public string $basePrice = '';

    public string $priceUnit = 'per_job';

    public string $durationEstimate = '';

    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();

        $this->categoryId = (string) ($this->categories->first()?->id ?? '');

        $this->showForm = true;
    }

    public function edit(Service $service): void
    {
        $this->resetForm();

        $this->editingId = $service->id;
        $this->name = $service->name;
        $this->categoryId = (string) $service->service_category_id;
        $this->description = (string) $service->description;
        $this->basePrice = $service->base_price === null ? '' : (string) $service->base_price;
        $this->priceUnit = $service->price_unit->value;
        $this->durationEstimate = (string) $service->duration_estimate;
        $this->isActive = $service->is_active;

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:service_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'basePrice' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'priceUnit' => ['required', Rule::enum(PriceUnit::class)],
            'durationEstimate' => ['nullable', 'string', 'max:100'],
            'isActive' => ['required', 'boolean'],
        ]);

        $attributes = [
            'service_category_id' => (int) $validated['categoryId'],
            'name' => $validated['name'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'base_price' => $validated['basePrice'] !== '' ? $validated['basePrice'] : null,
            'price_unit' => $validated['priceUnit'],
            'duration_estimate' => $validated['durationEstimate'] !== '' ? $validated['durationEstimate'] : null,
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId !== null) {
            Service::findOrFail($this->editingId)->update($attributes);

            Flux::toast(text: 'Jasa diperbarui.', variant: 'success');
        } else {
            Service::create([
                ...$attributes,
                'slug' => $this->uniqueSlug($validated['name']),
            ]);

            Flux::toast(text: 'Jasa ditambahkan.', variant: 'success');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Service $service): void
    {
        $service->delete();

        Flux::toast(text: 'Jasa dihapus.', variant: 'success');
    }

    /**
     * @return LengthAwarePaginator<int, Service>
     */
    #[Computed]
    public function services(): LengthAwarePaginator
    {
        return Service::query()
            ->with('category')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('service_category_id', $this->categoryFilter))
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * @return Collection<int, ServiceCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.admin.services.index', [
            'priceUnits' => PriceUnit::cases(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->categoryId = '';
        $this->description = '';
        $this->basePrice = '';
        $this->priceUnit = PriceUnit::PerJob->value;
        $this->durationEstimate = '';
        $this->isActive = true;

        $this->resetValidation();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Service::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
