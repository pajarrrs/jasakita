<?php

namespace App\Livewire\Partner;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Models\PartnerVerification;
use App\Models\User;
use App\Notifications\VerificationSubmitted;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.partner')]
class Verification extends Component
{
    use WithFileUploads;

    public string $fullName = '';

    public string $idNumber = '';

    public $idPhoto = null;

    public $selfiePhoto = null;

    public function mount(): void
    {
        $user = Auth::user();
        $verification = $user->partnerProfile?->verification;

        $this->fullName = $verification?->full_name ?? $user->name;
        $this->idNumber = (string) ($verification?->id_number ?? '');
    }

    public function submit(): void
    {
        $profile = Auth::user()->partnerProfileOrCreate();
        $verification = $profile->verification;

        if ($verification !== null && ! $verification->isRejected()) {
            Flux::toast(text: 'Pengajuanmu masih diproses admin.', variant: 'danger');

            return;
        }

        $isFirstSubmission = $verification === null;

        $validated = $this->validate([
            'fullName' => ['required', 'string', 'max:255'],
            'idNumber' => ['required', 'string', 'digits_between:8,20'],
            'idPhoto' => [$isFirstSubmission ? 'required' : 'nullable', 'image', 'max:4096'],
            'selfiePhoto' => [$isFirstSubmission ? 'required' : 'nullable', 'image', 'max:4096'],
        ]);

        $verification = PartnerVerification::updateOrCreate(
            ['partner_profile_id' => $profile->id],
            [
                'full_name' => $validated['fullName'],
                'id_number' => $validated['idNumber'],
                'id_photo_path' => $this->idPhoto !== null
                    ? $this->idPhoto->store('kyc', 'public')
                    : $verification?->id_photo_path,
                'selfie_photo_path' => $this->selfiePhoto !== null
                    ? $this->selfiePhoto->store('kyc', 'public')
                    : $verification?->selfie_photo_path,
                'status' => VerificationStatus::Pending,
                'rejection_reason' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
        );

        $this->reset('idPhoto', 'selfiePhoto');

        User::query()
            ->where('role', Role::Admin)
            ->get()
            ->each(fn (User $admin) => $admin->notify(new VerificationSubmitted($verification)));

        Flux::toast(text: 'Pengajuan verifikasi terkirim. Tunggu review admin ya.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.partner.verification', [
            'verification' => Auth::user()->partnerProfile?->verification,
        ]);
    }
}
