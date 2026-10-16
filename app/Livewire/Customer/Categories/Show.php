<?php

namespace App\Livewire\Customer\Categories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Show extends Component
{
    public ServiceCategory $category;

    public function mount(ServiceCategory $category): void
    {
        abort_unless($category->is_active, 404);

        $this->category = $category;
    }

    /**
     * @return Collection<int, Service>
     */
    #[Computed]
    public function services(): Collection
    {
        return $this->category->services()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.customer.categories.show');
    }
}
