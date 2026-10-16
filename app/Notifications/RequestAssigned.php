<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class RequestAssigned extends AppNotification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function title(): string
    {
        return 'Mitra sudah mengambil jobmu';
    }

    public function body(): string
    {
        return $this->serviceRequest->partner->user->name.' akan mengerjakan '.$this->serviceRequest->title.'.';
    }

    public function url(object $notifiable): ?string
    {
        return $this->requestUrl($this->serviceRequest, $notifiable);
    }

    public function icon(): string
    {
        return 'hand-raised';
    }

    public function color(): string
    {
        return 'blue';
    }
}
