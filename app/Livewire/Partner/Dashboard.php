<?php

namespace App\Livewire\Partner;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.partner')]
class Dashboard extends Component
{
    public function render(): View
    {
        $profile = Auth::user()->partnerProfile;

        $activeStatuses = array_map(
            fn (RequestStatus $status) => $status->value,
            RequestStatus::activeForPartner(),
        );

        $openRequests = $profile === null
            ? collect()
            : ServiceRequest::query()
                ->where('status', RequestStatus::Open->value)
                ->whereIn('service_id', $profile->services()->pluck('services.id'))
                ->whereDoesntHave('offers', fn ($query) => $query->where('partner_profile_id', $profile->id))
                ->with('service')
                ->latest()
                ->get();

        $upcomingJobs = $profile === null
            ? collect()
            : $profile->assignedRequests()
                ->whereIn('status', $activeStatuses)
                ->with(['service', 'customer'])
                ->withCount([
                    'messages as unread_messages_count' => fn ($query) => $query
                        ->where('sender_id', '!=', Auth::id())
                        ->whereNull('read_at'),
                ])
                ->orderByRaw('scheduled_at is null')
                ->orderBy('scheduled_at')
                ->take(3)
                ->get();

        return view('livewire.partner.dashboard', [
            'offeredServicesCount' => $profile?->services()->count() ?? 0,
            'isProfileComplete' => $profile !== null && filled($profile->bio) && filled($profile->city),
            'isAvailable' => $profile?->is_available ?? false,
            'openRequestsCount' => $openRequests->count(),
            'activeJobsCount' => $profile === null
                ? 0
                : $profile->assignedRequests()->whereIn('status', $activeStatuses)->count(),
            'ratingAverage' => $profile?->ratingAverage(),
            'reviewsCount' => $profile?->reviews()->count() ?? 0,
            'recentRequests' => $openRequests->take(3),
            'upcomingJobs' => $upcomingJobs,
        ]);
    }
}
