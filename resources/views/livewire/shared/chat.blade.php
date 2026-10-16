<div wire:poll.5s="refreshChat" class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="lg">Chat</flux:heading>
            <flux:text class="mt-1">Diskusikan detail pekerjaan langsung dengan {{ $this->counterpartName }}.</flux:text>
        </div>

        <flux:badge color="teal" size="sm" icon="chat-bubble-left-right">
            {{ $this->messages->count() }} pesan
        </flux:badge>
    </div>

    <flux:card class="flex flex-col gap-4">
        @if ($this->messages->isEmpty())
            <div class="py-6 text-center">
                <flux:icon name="chat-bubble-left-right" class="mx-auto size-6 text-zinc-300" />
                <p class="mt-2 text-sm text-zinc-500">
                    Belum ada pesan. Sapa {{ $this->counterpartName }} dulu untuk menyepakati detail pekerjaan.
                </p>
            </div>
        @else
            <div class="flex max-h-96 flex-col-reverse gap-3 overflow-y-auto">
                @foreach ($this->messages->reverse() as $message)
                    @php $isMine = $message->isFrom(auth()->user()); @endphp

                    <div
                        class="flex flex-col gap-1 {{ $isMine ? 'items-end' : 'items-start' }}"
                        wire:key="message-{{ $message->id }}"
                    >
                        <span class="px-1 text-[11px] text-zinc-400">
                            {{ $isMine ? 'Kamu' : $message->sender->name }}
                            &middot; {{ $message->created_at->translatedFormat('d M, H:i') }}
                        </span>

                        <div @class([
                            'max-w-[85%] rounded-2xl px-3.5 py-2 text-sm leading-relaxed whitespace-pre-line',
                            'rounded-br-md bg-teal-600 text-white' => $isMine,
                            'rounded-bl-md bg-zinc-100 text-zinc-800' => ! $isMine,
                        ])>
                            {{ $message->body }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($serviceRequest->isChatOpen())
            <form wire:submit="send" class="flex items-end gap-2 border-t border-zinc-200 pt-4">
                <div class="min-w-0 flex-1">
                    <flux:textarea
                        wire:model="body"
                        rows="2"
                        placeholder="Tulis pesan untuk {{ $this->counterpartName }}..."
                    />

                    @error('body')
                        <p class="mt-1 text-sm font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <flux:button type="submit" variant="primary" icon="paper-airplane" square class="mb-0.5" />
            </form>
        @else
            <p class="border-t border-zinc-200 pt-4 text-center text-sm text-zinc-500">
                Percakapan ditutup karena request ini sudah dibatalkan.
            </p>
        @endif
    </flux:card>
</div>
