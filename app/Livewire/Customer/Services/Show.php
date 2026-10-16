<?php

namespace App\Livewire\Customer\Services;

use App\Models\Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Show extends Component
{
    public Service $service;

    public function mount(Service $service): void
    {
        abort_unless($service->is_active, 404);

        $this->service = $service;
    }

    public function render(): View
    {
        $this->service->loadMissing('category');

        return view('livewire.customer.services.show');
    }
}
