<?php

namespace App\Livewire\Partner\Jobs;

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

#[Layout('layouts.partner')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    /**
     * Pilihan filter status job.
     *
     * @return array<string, string>
     */
    public static function filters(): array
    {
        return [
            '' => 'Job aktif',
            RequestStatus::Completed->value => 'Selesai',
            RequestStatus::Cancelled->value => 'Dibatalkan',
        ];
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, ServiceRequest>
     */
    #[Computed]
    public function jobs(): LengthAwarePaginator
    {
        $profile = Auth::user()->partnerProfileOrCreate();

        $statuses = match ($this->status) {
            RequestStatus::Completed->value => [RequestStatus::Completed],
            RequestStatus::Cancelled->value => [RequestStatus::Cancelled],
            default => RequestStatus::activeForPartner(),
        };

        return $profile->assignedRequests()
            ->whereIn('status', array_map(fn (RequestStatus $status) => $status->value, $statuses))
            ->with(['service.category', 'customer'])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('sender_id', '!=', Auth::id())
                    ->whereNull('read_at'),
            ])
            ->orderByRaw('scheduled_at is null')
            ->orderBy('scheduled_at')
            ->latest()
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.partner.jobs.index', [
            'filters' => self::filters(),
        ]);
    }
}
