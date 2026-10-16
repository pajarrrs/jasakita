<?php

namespace App\Livewire\Admin\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
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
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, ServiceRequest>
     */
    #[Computed]
    public function requests(): LengthAwarePaginator
    {
        return ServiceRequest::query()
            ->with(['customer', 'service', 'partner.user'])
            ->withCount('offers')
            ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                $keyword = '%'.$this->search.'%';

                $query->where('title', 'like', $keyword)
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $keyword))
                    ->orWhereHas('service', fn ($service) => $service->where('name', 'like', $keyword));
            }))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.admin.requests.index', [
            'statuses' => RequestStatus::cases(),
        ]);
    }
}
