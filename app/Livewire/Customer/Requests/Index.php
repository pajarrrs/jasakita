<?php

namespace App\Livewire\Customer\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.customer')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

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
        return Auth::user()
            ->serviceRequests()
            ->with(['service', 'partner.user'])
            ->withCount('offers')
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('sender_id', '!=', Auth::id())
                    ->whereNull('read_at'),
            ])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.customer.requests.index', [
            'statuses' => RequestStatus::cases(),
        ]);
    }
}
