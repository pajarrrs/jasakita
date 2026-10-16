<?php

namespace App\Livewire\Partner\Jobs;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Notifications\JobCompleted;
use App\Notifications\JobStarted;
use App\Notifications\RequestCancelled;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.partner')]
class Show extends Component
{
    public ServiceRequest $serviceRequest;

    public string $scheduledAt = '';

    public string $cancellationReason = '';

    public function mount(ServiceRequest $serviceRequest): void
    {
        abort_unless($serviceRequest->partner_profile_id === Auth::user()->partnerProfileOrCreate()->id, 403);

        $this->serviceRequest = $serviceRequest;
        $this->scheduledAt = $serviceRequest->scheduled_at?->format('Y-m-d\TH:i') ?? '';
    }

    public function confirmSchedule(): void
    {
        if (! in_array($this->serviceRequest->status, [RequestStatus::Assigned, RequestStatus::Scheduled], true)) {
            Flux::toast(text: 'Jadwal tidak bisa diubah pada tahap ini.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'scheduledAt' => ['required', 'date'],
        ]);

        $this->serviceRequest->update(['scheduled_at' => $validated['scheduledAt']]);

        if ($this->serviceRequest->status === RequestStatus::Assigned) {
            $this->serviceRequest->recordStatus(RequestStatus::Scheduled, Auth::user(), 'Jadwal dikonfirmasi mitra.');
        }

        $this->serviceRequest->refresh();

        Flux::toast(text: 'Jadwal lapangan disimpan.', variant: 'success');
    }

    public function start(): void
    {
        if (! in_array($this->serviceRequest->status, [RequestStatus::Assigned, RequestStatus::Scheduled], true)) {
            Flux::toast(text: 'Pekerjaan ini sudah berjalan atau sudah selesai.', variant: 'danger');

            return;
        }

        $this->serviceRequest->recordStatus(RequestStatus::InProgress, Auth::user(), 'Mitra mulai mengerjakan.');

        $this->serviceRequest->refresh();

        $this->serviceRequest->customer->notify(new JobStarted($this->serviceRequest));

        Flux::toast(text: 'Selamat bekerja!', variant: 'success');
    }

    public function complete(): void
    {
        if ($this->serviceRequest->status !== RequestStatus::InProgress) {
            Flux::toast(text: 'Tandai mulai dulu sebelum menandai selesai.', variant: 'danger');

            return;
        }

        $this->serviceRequest->update(['completed_at' => now()]);
        $this->serviceRequest->recordStatus(RequestStatus::Completed, Auth::user(), 'Mitra menandai pekerjaan selesai.');

        $this->serviceRequest->refresh();

        $this->serviceRequest->customer->notify(new JobCompleted($this->serviceRequest));

        Flux::toast(text: 'Pekerjaan selesai. Terima kasih!', variant: 'success');
    }

    public function cancel(): void
    {
        if (! $this->serviceRequest->status->isCancellableByPartner()) {
            Flux::toast(text: 'Job ini sudah tidak bisa dibatalkan.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'cancellationReason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = $validated['cancellationReason'];

        $this->serviceRequest->update([
            'cancellation_reason' => $reason !== '' ? $reason : null,
        ]);

        $this->serviceRequest->recordStatus(
            RequestStatus::Cancelled,
            Auth::user(),
            $reason !== '' ? "Dibatalkan mitra: {$reason}" : 'Dibatalkan mitra.',
        );

        $this->serviceRequest->refresh();

        $this->serviceRequest->customer->notify(new RequestCancelled($this->serviceRequest, 'mitra'));

        Flux::toast(text: 'Job dibatalkan.', variant: 'success');
    }

    public function render(): View
    {
        $this->serviceRequest->loadMissing([
            'service.category',
            'customer',
            'review.customer',
            'statusHistories.changedBy',
        ]);

        return view('livewire.partner.jobs.show');
    }
}
