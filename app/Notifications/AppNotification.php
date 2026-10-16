<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Notifications\Notification;

abstract class AppNotification extends Notification
{
    /**
     * Judul singkat notifikasi.
     */
    abstract public function title(): string;

    /**
     * Isi notifikasi.
     */
    abstract public function body(): string;

    /**
     * URL tujuan ketika notifikasi diklik.
     */
    abstract public function url(object $notifiable): ?string;

    /**
     * Nama ikon Flux untuk tampilan daftar notifikasi.
     */
    public function icon(): string
    {
        return 'bell';
    }

    /**
     * Warna badge Flux untuk tampilan daftar notifikasi.
     */
    public function color(): string
    {
        return 'teal';
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url($notifiable),
            'icon' => $this->icon(),
            'color' => $this->color(),
        ];
    }

    /**
     * URL halaman detail request sesuai peran penerima.
     */
    protected function requestUrl(ServiceRequest $serviceRequest, object $notifiable): string
    {
        if ($notifiable instanceof User && $notifiable->role->isCustomer()) {
            return route('customer.requests.show', $serviceRequest);
        }

        return route('partner.jobs.show', $serviceRequest);
    }
}
