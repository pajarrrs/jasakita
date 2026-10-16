<?php

namespace App\Livewire\Admin\Verifications;

use App\Enums\VerificationStatus;
use App\Models\PartnerVerification;
use App\Notifications\VerificationReviewed;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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
    public string $status = '';

    public bool $showDetail = false;

    public ?int $selectedId = null;

    public string $rejectionReason = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function inspect(PartnerVerification $verification): void
    {
        $this->selectedId = $verification->id;
        $this->rejectionReason = '';

        $this->resetValidation();

        $this->showDetail = true;
    }

    public function approve(): void
    {
        $verification = $this->selectedVerification();

        if ($verification === null || ! $verification->isPending()) {
            Flux::toast(text: 'Pengajuan ini sudah diproses.', variant: 'danger');

            return;
        }

        $verification->update([
            'status' => VerificationStatus::Approved,
            'rejection_reason' => null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $verification->partner->user->notify(new VerificationReviewed($verification));

        $this->showDetail = false;

        unset($this->verifications, $this->pendingCount);

        Flux::toast(text: 'Mitra berhasil diverifikasi.', variant: 'success');
    }

    public function reject(): void
    {
        $verification = $this->selectedVerification();

        if ($verification === null || ! $verification->isPending()) {
            Flux::toast(text: 'Pengajuan ini sudah diproses.', variant: 'danger');

            return;
        }

        $validated = $this->validate([
            'rejectionReason' => ['required', 'string', 'max:255'],
        ]);

        $verification->update([
            'status' => VerificationStatus::Rejected,
            'rejection_reason' => $validated['rejectionReason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $verification->partner->user->notify(new VerificationReviewed($verification));

        $this->showDetail = false;

        unset($this->verifications, $this->pendingCount);

        Flux::toast(text: 'Pengajuan verifikasi ditolak.', variant: 'success');
    }

    #[Computed]
    public function selectedVerification(): ?PartnerVerification
    {
        return $this->selectedId === null
            ? null
            : PartnerVerification::query()
                ->with(['partner.user', 'reviewer'])
                ->find($this->selectedId);
    }

    /**
     * @return LengthAwarePaginator<int, PartnerVerification>
     */
    #[Computed]
    public function verifications(): LengthAwarePaginator
    {
        return PartnerVerification::query()
            ->with(['partner.user', 'reviewer'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('updated_at')
            ->paginate(10);
    }

    #[Computed]
    public function pendingCount(): int
    {
        return PartnerVerification::query()
            ->where('status', VerificationStatus::Pending->value)
            ->count();
    }

    public function render(): View
    {
        return view('livewire.admin.verifications.index', [
            'statuses' => VerificationStatus::cases(),
        ]);
    }
}
