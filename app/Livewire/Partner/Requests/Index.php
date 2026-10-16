<?php

namespace App\Livewire\Partner\Requests;

use App\Enums\RequestStatus;
use App\Models\PartnerProfile;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.partner')]
class Index extends Component
{
    use TakesDirectJobs, WithPagination;

    #[Computed]
    public function profile(): PartnerProfile
    {
        return Auth::user()->partnerProfileOrCreate();
    }

    /**
     * Request terbuka untuk jasa yang ditawarkan mitra ini dan belum ia tanggapi.
     *
     * @return LengthAwarePaginator<int, ServiceRequest>
     */
    #[Computed]
    public function requests(): LengthAwarePaginator
    {
        $profile = $this->profile;

        return ServiceRequest::query()
            ->where('status', RequestStatus::Open)
            ->whereIn('service_id', $profile->services()->pluck('services.id'))
            ->whereDoesntHave('offers', fn ($query) => $query->where('partner_profile_id', $profile->id))
            ->with(['service.category', 'customer'])
            ->latest()
            ->paginate(10);
    }

    public function takeJob(ServiceRequest $serviceRequest): void
    {
        if ($this->takeDirectJob($serviceRequest)) {
            $this->redirect(route('partner.jobs.show', $serviceRequest), navigate: true);
        }
    }

    public function render(): View
    {
        return view('livewire.partner.requests.index');
    }
}
