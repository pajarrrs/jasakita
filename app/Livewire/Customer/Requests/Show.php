<?php

namespace App\Livewire\Customer\Requests;

use App\Enums\OfferStatus;
use App\Enums\RequestStatus;
use App\Models\Offer;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Notifications\CompletionConfirmed;
use App\Notifications\OfferAccepted;
use App\Notifications\OfferRejected;
use App\Notifications\RequestCancelled;
use App\Notifications\ReviewReceived;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Show extends Component
{
    public ServiceRequest $serviceRequest;

    public string $scheduledAt = '';

    public string $cancellationReason = '';

    public string $rating = '';

    public string $comment = '';

    public function mount(ServiceRequest $serviceRequest): void
    {
        abort_unless($serviceRequest->customer_id === Auth::id(), 403);

        $this->serviceRequest = $serviceRequest;

        $this->scheduledAt = $serviceRequest->scheduled_at?->format('Y-m-d\TH:i') ?? '';
    }

    public function acceptOffer(Offer $offer): void
    {
        abort_unless($offer->service_request_id === $this->serviceRequest->id, 404);

        $serviceRequest = $this->serviceRequest;

        if (! $serviceRequest->isOpen()) {
            Flux::toast(text: 'Request ini sudah tidak menerima penawaran.', variant: 'danger');

            return;
        }

        if (! $offer->isPending()) {
            Flux::toast(text: 'Penawaran ini sudah tidak aktif.', variant: 'danger');

            return;
        }

        $partnerName = $offer->partner->user->name;

        DB::transaction(function () use ($serviceRequest, $offer, $partnerName): void {
            $offer->update(['status' => OfferStatus::Accepted]);

            $serviceRequest->offers()
                ->whereKeyNot($offer->id)
                ->where('status', OfferStatus::Pending->value)
                ->update(['status' => OfferStatus::Rejected->value]);

            $serviceRequest->update([
                'partner_profile_id' => $offer->partner_profile_id,
                'agreed_price' => $offer->price,
            ]);

            $serviceRequest->recordStatus(
                RequestStatus::Assigned,
                Auth::user(),
                "Penawaran dari {$partnerName} diterima.",
            );

            if ($serviceRequest->scheduled_at !== null) {
                $serviceRequest->recordStatus(
                    RequestStatus::Scheduled,
                    Auth::user(),
                    'Jadwal memakai waktu yang kamu minta sebelumnya.',
                );
            }
        });

        $this->serviceRequest->refresh()->load('partner.user');

        $offer->partner->user->notify(new OfferAccepted($offer));

        $this->serviceRequest->offers()
            ->where('status', OfferStatus::Rejected->value)
            ->with('partner.user')
            ->get()
            ->each(fn (Offer $rejectedOffer) => $rejectedOffer->partner->user->notify(new OfferRejected($rejectedOffer)));

        Flux::toast(text: "{$partnerName} mengerjakan request ini.", variant: 'success');
    }

    public function confirmSchedule(): void
    {
        $serviceRequest = $this->serviceRequest;

        if (! in_array($serviceRequest->status, [RequestStatus::Assigned, RequestStatus::Scheduled], true)) {
            Flux::toast(text: 'Jadwal hanya bisa diatur setelah mitra ditetapkan.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'scheduledAt' => ['required', 'date', 'after:now'],
        ]);

        $serviceRequest->update(['scheduled_at' => $validated['scheduledAt']]);

        if ($serviceRequest->status === RequestStatus::Assigned) {
            $serviceRequest->recordStatus(RequestStatus::Scheduled, Auth::user(), 'Jadwal dikonfirmasi.');
        }

        $this->serviceRequest->refresh();

        Flux::toast(text: 'Jadwal disimpan.', variant: 'success');
    }

    public function confirmCompletion(): void
    {
        $serviceRequest = $this->serviceRequest;

        if ($serviceRequest->status !== RequestStatus::InProgress) {
            Flux::toast(text: 'Pekerjaan ini belum bisa ditandai selesai.', variant: 'danger');

            return;
        }

        $serviceRequest->update(['completed_at' => now()]);
        $serviceRequest->recordStatus(RequestStatus::Completed, Auth::user(), 'Pelanggan menandai pekerjaan selesai.');

        $this->serviceRequest->refresh()->loadMissing('partner.user');

        $this->serviceRequest->partner?->user?->notify(new CompletionConfirmed($this->serviceRequest));

        Flux::toast(text: 'Kerja sama selesai. Jangan lupa beri ulasan.', variant: 'success');
    }

    public function cancel(): void
    {
        $serviceRequest = $this->serviceRequest;

        if (! $serviceRequest->status->isCancellableByCustomer()) {
            Flux::toast(text: 'Request ini sudah tidak bisa dibatalkan.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'cancellationReason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = $validated['cancellationReason'];

        $pendingOffers = $serviceRequest->offers()
            ->where('status', OfferStatus::Pending->value)
            ->with('partner.user')
            ->get();

        DB::transaction(function () use ($serviceRequest, $reason): void {
            $serviceRequest->offers()
                ->where('status', OfferStatus::Pending->value)
                ->update(['status' => OfferStatus::Withdrawn->value]);

            $serviceRequest->update([
                'cancellation_reason' => $reason !== '' ? $reason : null,
            ]);

            $serviceRequest->recordStatus(
                RequestStatus::Cancelled,
                Auth::user(),
                $reason !== '' ? "Dibatalkan pelanggan: {$reason}" : 'Dibatalkan pelanggan.',
            );
        });

        $this->serviceRequest->refresh()->loadMissing('partner.user');

        $this->serviceRequest->partner?->user?->notify(new RequestCancelled($this->serviceRequest, 'pelanggan'));

        $pendingOffers->each(
            fn (Offer $pendingOffer) => $pendingOffer->partner->user->notify(new RequestCancelled($this->serviceRequest, 'pelanggan')),
        );

        Flux::toast(text: 'Request dibatalkan.', variant: 'success');
    }

    public function submitReview(): void
    {
        $serviceRequest = $this->serviceRequest;

        if (! $serviceRequest->isCompleted()) {
            Flux::toast(text: 'Ulasan bisa dikirim setelah pekerjaan selesai.', variant: 'danger');

            return;
        }

        if ($serviceRequest->isReviewed()) {
            Flux::toast(text: 'Ulasan untuk request ini sudah dikirim.', variant: 'danger');

            return;
        }

        if ($serviceRequest->partner_profile_id === null) {
            Flux::toast(text: 'Request ini tidak punya mitra pengerjaan.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::create([
            'service_request_id' => $serviceRequest->id,
            'customer_id' => Auth::id(),
            'partner_profile_id' => $serviceRequest->partner_profile_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] !== '' ? $validated['comment'] : null,
        ]);

        $this->serviceRequest->refresh()->load('review');

        $this->serviceRequest->partner?->user?->notify(new ReviewReceived($review));

        Flux::toast(text: 'Terima kasih atas ulasannya!', variant: 'success');
    }

    public function render(): View
    {
        $this->serviceRequest->load([
            'service.category',
            'partner.user',
            'partner.verification',
            'offers.partner.user',
            'offers.partner.verification',
            'review',
            'statusHistories.changedBy',
        ]);

        return view('livewire.customer.requests.show');
    }
}
