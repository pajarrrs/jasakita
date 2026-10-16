<?php

namespace App\Notifications;

use App\Models\Offer;

class OfferRejected extends AppNotification
{
    public function __construct(public Offer $offer) {}

    public function title(): string
    {
        return 'Penawaran belum terpilih';
    }

    public function body(): string
    {
        return 'Request '.$this->offer->serviceRequest->title.' ditangani mitra lain. Coba request lain di daftar request masuk.';
    }

    public function url(object $notifiable): ?string
    {
        return route('partner.requests.index');
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
