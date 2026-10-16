<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class CompletionConfirmed extends AppNotification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function title(): string
    {
        return 'Pelanggan mengonfirmasi selesai';
    }

    public function body(): string
    {
        return 'Kerja bagus! '.$this->serviceRequest->title.' sudah dikonfirmasi selesai oleh pelanggan.';
    }

    public function url(object $notifiable): ?string
    {
        return route('partner.jobs.show', $this->serviceRequest);
    }

    public function icon(): string
    {
        return 'check-badge';
    }

    public function color(): string
    {
        return 'emerald';
    }
}
