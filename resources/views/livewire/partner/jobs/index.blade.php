<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Job Saya</flux:heading>
        <flux:text class="mt-1">Pekerjaan yang sudah menjadi tanggung jawabmu.</flux:text>
    </div>

    <flux:select wire:model.live="status" label="Filter status">
        @foreach ($filters as $value => $label)
            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->jobs->isEmpty())
        <x-ui.empty-state
            icon="clipboard-document-list"
            title="Belum ada job di sini"
            description="Ambil job dari request masuk atau terima penawaran pelanggan untuk mengisi daftar ini."
        >
            <flux:button :href="route('partner.requests.index')" wire:navigate variant="filled">
                Lihat request masuk
            </flux:button>
        </x-ui.empty-state>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($this->jobs as $job)
                <a
                    href="{{ route('partner.jobs.show', $job) }}"
                    wire:navigate
                    wire:key="job-{{ $job->id }}"
                    class="group"
                >
                    <flux:card class="flex flex-col gap-3 transition group-hover:border-teal-300">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900">{{ $job->title }}</p>
                                <p class="truncate text-xs text-zinc-500">
                                    {{ $job->customer->name }} &middot; {{ $job->service->name }}
                                </p>

                                @if (($job->unread_messages_count ?? 0) > 0)
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-teal-600 px-2 py-0.5 text-[11px] font-semibold text-white">
                                        <flux:icon name="chat-bubble-left-right" class="size-3" />
                                        {{ $job->unread_messages_count }} pesan baru
                                    </span>
                                @endif
                            </div>

                            <x-ui.status-pill :status="$job->status" class="shrink-0" />
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
                            <span class="flex items-center gap-1">
                                <flux:icon name="calendar-days" class="size-3.5" />
                                {{ $job->scheduled_at?->translatedFormat('d F Y, H:i') ?? 'Jadwal menyusul' }}
                            </span>

                            <span class="flex items-center gap-1">
                                <flux:icon name="map-pin" class="size-3.5" />
                                {{ $job->city ?? $job->address }}
                            </span>

                            <span class="flex items-center gap-1">
                                <flux:icon name="banknotes" class="size-3.5" />
                                <x-ui.price :amount="$job->agreed_price ?? $job->service->base_price" :unit="$job->service->price_unit->label()" />
                            </span>
                        </div>
                    </flux:card>
                </a>
            @endforeach
        </div>

        @if ($this->jobs->hasPages())
            <flux:pagination :paginator="$this->jobs" />
        @endif
    @endif
</div>
