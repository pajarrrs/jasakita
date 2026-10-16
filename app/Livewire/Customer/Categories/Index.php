<?php

namespace App\Livewire\Customer\Categories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.customer')]
class Index extends Component
{
    #[Url(as: 'cari', except: '')]
    public string $search = '';

    /**
     * @return Collection<int, ServiceCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::query()
            ->where('is_active', true)
            ->withCount([
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Service>
     */
    #[Computed]
    public function matchingServices(): Collection
    {
        $term = trim($this->search);

        if ($term === '') {
            return new Collection;
        }

        return Service::query()
            ->where('is_active', true)
            ->where('name', 'like', '%'.$term.'%')
            ->with('category')
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.customer.categories.index');
    }
}
