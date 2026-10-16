<?php

namespace App\Notifications;

use App\Models\Offer;

class OfferAccepted extends AppNotification
{
    public function __construct(public Offer $offer) {}

    public function title(): string
    {
        return 'Penawaranmu diterima!';
    }

    public function body(): string
    {
        return 'Pelanggan memilih penawaranmu untuk '.$this->offer->serviceRequest->title.'. Segera atur jadwalnya.';
    }

    public function url(object $notifiable): ?string
    {
        return route('partner.jobs.show', $this->offer->serviceRequest);
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
