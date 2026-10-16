<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Support\Str;

class MessageReceived extends AppNotification
{
    public function __construct(public Message $message) {}

    public function title(): string
    {
        return 'Pesan baru dari '.$this->message->sender->name;
    }

    public function body(): string
    {
        return Str::limit($this->message->body, 90);
    }

    public function url(object $notifiable): ?string
    {
        return $this->requestUrl($this->message->serviceRequest, $notifiable);
    }

    public function icon(): string
    {
        return 'chat-bubble-left-right';
    }
}
