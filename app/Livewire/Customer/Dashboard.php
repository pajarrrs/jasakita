<?php

namespace App\Livewire\Customer;

use App\Enums\RequestStatus;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->withCount([
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(4)
            ->get();

        $activeStatuses = [
            RequestStatus::Open->value,
            RequestStatus::Assigned->value,
            RequestStatus::Scheduled->value,
            RequestStatus::InProgress->value,
        ];

        return view('livewire.customer.dashboard', [
            'categories' => $categories,
            'activeRequestCount' => $user->serviceRequests()->whereIn('status', $activeStatuses)->count(),
            'completedRequestCount' => $user->serviceRequests()->where('status', RequestStatus::Completed->value)->count(),
            'recentRequests' => $user->serviceRequests()
                ->with(['service', 'partner.user'])
                ->withCount('offers')
                ->withCount([
                    'messages as unread_messages_count' => fn ($query) => $query
                        ->where('sender_id', '!=', $user->id)
                        ->whereNull('read_at'),
                ])
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
