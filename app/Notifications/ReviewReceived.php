<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Support\Str;

class ReviewReceived extends AppNotification
{
    public function __construct(public Review $review) {}

    public function title(): string
    {
        return 'Ulasan baru '.$this->review->rating.' bintang';
    }

    public function body(): string
    {
        return Str::limit($this->review->comment ?? 'Pelanggan memberi rating untuk pekerjaanmu.', 90);
    }

    public function url(object $notifiable): ?string
    {
        return route('partner.profile');
    }

    public function icon(): string
    {
        return 'star';
    }

    public function color(): string
    {
        return 'amber';
    }
}
