<?php

namespace App\Livewire\Admin;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Models\PartnerVerification;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        $activeStatuses = [
            RequestStatus::Open->value,
            RequestStatus::Assigned->value,
            RequestStatus::Scheduled->value,
            RequestStatus::InProgress->value,
        ];

        return view('livewire.admin.dashboard', [
            'categoryCount' => ServiceCategory::count(),
            'serviceCount' => Service::count(),
            'userCount' => User::count(),
            'partnerCount' => User::where('role', Role::Partner)->count(),
            'requestCount' => ServiceRequest::count(),
            'activeRequestCount' => ServiceRequest::whereIn('status', $activeStatuses)->count(),
            'pendingVerificationCount' => PartnerVerification::query()
                ->where('status', VerificationStatus::Pending->value)
                ->count(),
            'recentRequests' => ServiceRequest::query()
                ->with(['customer', 'service', 'partner.user'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
