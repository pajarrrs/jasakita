<?php

namespace App\Notifications;

use App\Models\PartnerVerification;

class VerificationSubmitted extends AppNotification
{
    public function __construct(public PartnerVerification $verification) {}

    public function title(): string
    {
        return 'Pengajuan verifikasi mitra';
    }

    public function body(): string
    {
        return $this->verification->full_name.' mengirim data verifikasi identitas untuk diperiksa.';
    }

    public function url(object $notifiable): ?string
    {
        return route('admin.verifications.index');
    }

    public function icon(): string
    {
        return 'document-text';
    }

    public function color(): string
    {
        return 'violet';
    }
}
