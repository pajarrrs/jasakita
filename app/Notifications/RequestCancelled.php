<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Models\User;

class RequestCancelled extends AppNotification
{
    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $cancelledBy,
    ) {}

    public function title(): string
    {
        return $this->serviceRequest->canBeChatted() ? 'Job dibatalkan' : 'Request dibatalkan';
    }

    public function body(): string
    {
        return $this->serviceRequest->title.' dibatalkan oleh '.$this->cancelledBy.'.';
    }

    public function url(object $notifiable): ?string
    {
        if ($notifiable instanceof User && $notifiable->role->isCustomer()) {
            return route('customer.requests.show', $this->serviceRequest);
        }

        return $this->serviceRequest->canBeChatted()
            ? route('partner.jobs.show', $this->serviceRequest)
            : route('partner.requests.index');
    }

    public function icon(): string
    {
        return 'x-circle';
    }

    public function color(): string
    {
        return 'rose';
    }
}
