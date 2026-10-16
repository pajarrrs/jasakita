<?php

namespace App\Livewire\Shared;

use App\Models\Message;
use App\Models\ServiceRequest;
use App\Notifications\MessageReceived;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Ruang chat pelanggan dan mitra untuk satu request pekerjaan.
 */
class Chat extends Component
{
    #[Locked]
    public ServiceRequest $serviceRequest;

    public string $body = '';

    public function mount(ServiceRequest $serviceRequest): void
    {
        $this->authorizeParticipant($serviceRequest);

        $this->serviceRequest = $serviceRequest;

        $this->markIncomingAsRead();
    }

    public function send(): void
    {
        $this->authorizeParticipant($this->serviceRequest);

        if (! $this->serviceRequest->isChatOpen()) {
            return;
        }

        $validated = $this->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $message = Message::create([
            'service_request_id' => $this->serviceRequest->id,
            'sender_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $this->reset('body');
        unset($this->messages);

        $this->serviceRequest->load('messages.sender');

        $this->notifyCounterpart($message);
    }

    /**
     * Dipanggil berkala oleh wire:poll untuk mengambil pesan baru.
     */
    public function refreshChat(): void
    {
        $this->markIncomingAsRead();
        unset($this->messages);

        $this->serviceRequest->load('messages.sender');
    }

    /**
     * @return Collection<int, Message>
     */
    #[Computed]
    public function messages(): Collection
    {
        return $this->serviceRequest->messages()->with('sender')->get();
    }

    /**
     * Nama lawan bicara yang tampil di header chat.
     */
    #[Computed]
    public function counterpartName(): string
    {
        $user = Auth::user();

        if ($this->serviceRequest->customer_id === $user->id) {
            return $this->serviceRequest->partner?->user?->name ?? 'Mitra';
        }

        return $this->serviceRequest->customer->name;
    }

    public function render(): View
    {
        return view('livewire.shared.chat');
    }

    private function markIncomingAsRead(): void
    {
        Message::query()
            ->where('service_request_id', $this->serviceRequest->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function notifyCounterpart(Message $message): void
    {
        $recipient = $message->sender_id === $this->serviceRequest->customer_id
            ? $this->serviceRequest->partner?->user
            : $this->serviceRequest->customer;

        $recipient?->notify(new MessageReceived($message));
    }

    private function authorizeParticipant(ServiceRequest $serviceRequest): void
    {
        $user = Auth::user();

        $isCustomer = $serviceRequest->customer_id === $user->id;
        $isPartner = $serviceRequest->partner_profile_id !== null
            && $serviceRequest->partner_profile_id === $user->partnerProfile?->id;

        abort_unless($isCustomer || $isPartner, 403);
    }
}
