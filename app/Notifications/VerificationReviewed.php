<?php

namespace App\Notifications;

use App\Models\PartnerVerification;

class VerificationReviewed extends AppNotification
{
    public function __construct(public PartnerVerification $verification) {}

    public function title(): string
    {
        return $this->verification->isApproved() ? 'Verifikasi disetujui' : 'Verifikasi ditolak';
    }

    public function body(): string
    {
        return $this->verification->isApproved()
            ? 'Identitasmu terverifikasi. Badge terverifikasi kini tampil di penawaranmu.'
            : 'Alasan: '.($this->verification->rejection_reason ?? 'Data belum sesuai.');
    }

    public function url(object $notifiable): ?string
    {
        return route('partner.verification');
    }

    public function icon(): string
    {
        return $this->verification->status->icon();
    }

    public function color(): string
    {
        return $this->verification->status->color();
    }
}
