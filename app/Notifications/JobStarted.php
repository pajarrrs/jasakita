<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class JobStarted extends AppNotification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function title(): string
    {
        return 'Pekerjaan dimulai';
    }

    public function body(): string
    {
        return $this->serviceRequest->partner->user->name.' mulai mengerjakan '.$this->serviceRequest->title.'.';
    }

    public function url(object $notifiable): ?string
    {
        return $this->requestUrl($this->serviceRequest, $notifiable);
    }

    public function icon(): string
    {
        return 'wrench-screwdriver';
    }

    public function color(): string
    {
        return 'blue';
    }
}
