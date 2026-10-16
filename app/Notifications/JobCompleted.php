<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class JobCompleted extends AppNotification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function title(): string
    {
        return 'Pekerjaan selesai';
    }

    public function body(): string
    {
        return $this->serviceRequest->title.' sudah ditandai selesai. Jangan lupa beri ulasan untuk mitranya.';
    }

    public function url(object $notifiable): ?string
    {
        return $this->requestUrl($this->serviceRequest, $notifiable);
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
