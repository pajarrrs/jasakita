<div>
    <flux:dropdown position="bottom" align="end" wire:poll.30s>
        <button
            type="button"
            class="relative flex size-9 shrink-0 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900"
            aria-label="Notifikasi"
        >
            <flux:icon name="bell" class="size-5" />

            @if ($this->unreadCount > 0)
                <span class="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                    {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
                </span>
            @endif
        </button>

        <flux:menu class="w-80 max-w-[calc(100vw-2rem)]">
            <div class="flex items-center justify-between gap-2 px-2 py-1">
                <span class="text-sm font-semibold text-zinc-900">Notifikasi</span>

                @if ($this->unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="text-xs font-medium text-teal-700 transition hover:text-teal-800"
                    >
                        Tandai semua dibaca
                    </button>
                @endif
            </div>

            <flux:menu.separator />

            @if ($this->notifications->isEmpty())
                <div class="px-3 py-8 text-center">
                    <flux:icon name="bell-slash" class="mx-auto size-6 text-zinc-300" />
                    <p class="mt-2 text-sm text-zinc-500">Belum ada notifikasi</p>
                </div>
            @else
                <div class="max-h-80 overflow-y-auto">
                    @foreach ($this->notifications as $notification)
                        <button
                            type="button"
                            wire:key="notification-{{ $notification->id }}"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            @class([
                                'flex w-full items-start gap-3 px-3 py-2.5 text-left transition hover:bg-zinc-50',
                                'bg-teal-50/50' => $notification->read_at === null,
                            ])
                        >
                            <span @class([
                                'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg',
                                'bg-teal-50 text-teal-600' => $notification->read_at === null,
                                'bg-zinc-100 text-zinc-400' => $notification->read_at !== null,
                            ])>
                                <flux:icon :name="$notification->data['icon'] ?? 'bell'" class="size-4" />
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2">
                                    <span @class([
                                        'truncate text-sm text-zinc-900',
                                        'font-semibold' => $notification->read_at === null,
                                        'font-medium' => $notification->read_at !== null,
                                    ])>
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </span>

                                    @if ($notification->read_at === null)
                                        <span class="size-1.5 shrink-0 rounded-full bg-teal-500"></span>
                                    @endif
                                </span>

                                <span class="mt-0.5 line-clamp-2 block text-xs leading-relaxed text-zinc-500">
                                    {{ $notification->data['body'] ?? '' }}
                                </span>

                                <span class="mt-1 block text-[11px] text-zinc-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>
