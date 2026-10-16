<?php

namespace App\Notifications;

use App\Models\Offer;

class OfferReceived extends AppNotification
{
    public function __construct(public Offer $offer) {}

    public function title(): string
    {
        return 'Penawaran baru dari '.$this->offer->partner->user->name;
    }

    public function body(): string
    {
        $price = 'Rp '.number_format((float) $this->offer->price, 0, ',', '.');

        return $price.' untuk '.$this->offer->serviceRequest->title.'.';
    }

    public function url(object $notifiable): ?string
    {
        return $this->requestUrl($this->offer->serviceRequest, $notifiable);
    }

    public function icon(): string
    {
        return 'banknotes';
    }
}
