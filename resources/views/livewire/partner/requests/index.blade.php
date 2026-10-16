<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Request Masuk</flux:heading>
        <flux:text class="mt-1">Request pelanggan untuk jasa yang kamu tawarkan.</flux:text>
    </div>

    @if (! $this->profile->is_available)
        <div class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-start gap-3">
                <flux:icon name="exclamation-triangle" class="size-5 shrink-0 text-amber-500" />

                <div class="min-w-0">
                    <p class="text-sm font-medium text-zinc-900">Kamu sedang tidak menerima job</p>
                    <p class="mt-0.5 text-sm text-zinc-600">
                        Request tetap tampil di sini, tapi kamu belum bisa mengirim penawaran atau mengambil job.
                    </p>
                </div>
            </div>

            <flux:button :href="route('partner.profile')" wire:navigate variant="filled" size="sm" class="self-start">
                Aktifkan
            </flux:button>
        </div>
    @endif

    @if ($this->requests->isEmpty())
        <x-ui.empty-state
            icon="inbox"
            title="Belum ada request masuk"
            description="Request baru akan muncul di sini. Pastikan kamu sudah memilih layanan yang dikerjakan."
        >
            <flux:button :href="route('partner.services.index')" wire:navigate variant="filled">
                Kelola layanan
            </flux:button>
        </x-ui.empty-state>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($this->requests as $item)
                <flux:card class="flex flex-col gap-3" wire:key="request-{{ $item->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-zinc-900">{{ $item->title }}</p>
                            <p class="truncate text-xs text-zinc-500">
                                {{ $item->service->name }}
                                &middot; {{ $item->city ?? 'kota belum diisi' }}
                                &middot; {{ $item->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <flux:badge :color="$item->mode->color()" size="sm" class="shrink-0">
                            {{ $item->mode->label() }}
                        </flux:badge>
                    </div>

                    @if ($item->description)
                        <p class="line-clamp-2 text-sm text-zinc-600">{{ $item->description }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
                        <span class="flex items-center gap-1">
                            <flux:icon name="calendar-days" class="size-3.5" />
                            {{ $item->scheduled_at?->translatedFormat('d F Y, H:i') ?? 'Jadwal menyusul' }}
                        </span>

                        <span class="flex items-center gap-1">
                            <flux:icon name="banknotes" class="size-3.5" />
                            Patokan:
                            <x-ui.price :amount="$item->service->base_price" :unit="$item->service->price_unit->label()" />
                        </span>
                    </div>

                    <div class="flex justify-end gap-2">
                        @if ($item->mode->isDirect())
                            <flux:button
                                size="sm"
                                variant="filled"
                                :href="route('partner.requests.show', $item)"
                                wire:navigate
                            >
                                Lihat detail
                            </flux:button>

                            <flux:button
                                size="sm"
                                variant="primary"
                                icon="hand-raised"
                                wire:click="takeJob({{ $item->id }})"
                                wire:confirm="Ambil job ini? Kamu akan langsung menjadi mitra pelanggan ini."
                            >
                                Ambil job
                            </flux:button>
                        @else
                            <flux:button
                                size="sm"
                                variant="primary"
                                icon="chat-bubble-left-right"
                                :href="route('partner.requests.show', $item)"
                                wire:navigate
                            >
                                Kirim penawaran
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            @endforeach
        </div>

        @if ($this->requests->hasPages())
            <flux:pagination :paginator="$this->requests" />
        @endif
    @endif
</div>
