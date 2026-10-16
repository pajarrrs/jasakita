<?php

namespace App\Livewire\Partner\Requests;

use App\Enums\OfferStatus;
use App\Enums\RequestMode;
use App\Models\Offer;
use App\Models\PartnerProfile;
use App\Models\ServiceRequest;
use App\Notifications\OfferReceived;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.partner')]
class Show extends Component
{
    use TakesDirectJobs;

    public ServiceRequest $serviceRequest;

    public string $price = '';

    public string $durationEstimate = '';

    public string $message = '';

    public function mount(ServiceRequest $serviceRequest): void
    {
        $profile = $this->profile();

        abort_unless($serviceRequest->isOpen(), 404);
        abort_unless($profile->services()->whereKey($serviceRequest->service_id)->exists(), 403);

        $this->serviceRequest = $serviceRequest;

        $offer = $this->myOffer();

        $this->price = $offer !== null
            ? (string) (float) $offer->price
            : (string) (float) $profile->effectivePriceFor($serviceRequest->service);
        $this->durationEstimate = (string) ($offer?->duration_estimate ?? $serviceRequest->service->duration_estimate);
        $this->message = (string) $offer?->message;
    }

    public function sendOffer(): void
    {
        $profile = $this->profile();

        if (! $profile->is_available) {
            Flux::toast(text: 'Aktifkan status siap menerima job dulu.', variant: 'danger');

            return;
        }

        if (! $this->serviceRequest->isOpen()) {
            Flux::toast(text: 'Request ini sudah ditutup.', variant: 'danger');

            return;
        }

        if ($this->serviceRequest->mode !== RequestMode::Quote) {
            Flux::toast(text: 'Request ini memakai pesan langsung, ambil jobnya.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'price' => ['required', 'numeric', 'min:1000', 'max:999999999'],
            'durationEstimate' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $offer = Offer::updateOrCreate(
            [
                'service_request_id' => $this->serviceRequest->id,
                'partner_profile_id' => $profile->id,
            ],
            [
                'price' => $validated['price'],
                'duration_estimate' => $validated['durationEstimate'] !== '' ? $validated['durationEstimate'] : null,
                'message' => $validated['message'] !== '' ? $validated['message'] : null,
                'status' => OfferStatus::Pending,
            ],
        );

        unset($this->myOffer);

        if ($offer->wasRecentlyCreated) {
            $this->serviceRequest->customer->notify(new OfferReceived($offer));
        }

        Flux::toast(text: 'Penawaran terkirim ke pelanggan.', variant: 'success');
    }

    public function takeJobNow(): void
    {
        if ($this->takeDirectJob($this->serviceRequest)) {
            $this->redirect(route('partner.jobs.show', $this->serviceRequest), navigate: true);
        }
    }

    #[Computed]
    public function profile(): PartnerProfile
    {
        return Auth::user()->partnerProfileOrCreate();
    }

    #[Computed]
    public function myOffer(): ?Offer
    {
        return $this->serviceRequest->offers()
            ->where('partner_profile_id', $this->profile->id)
            ->first();
    }

    public function render(): View
    {
        $this->serviceRequest->loadMissing('service.category', 'customer');

        return view('livewire.partner.requests.show');
    }
}
