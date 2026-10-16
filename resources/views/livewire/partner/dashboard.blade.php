<div class="flex flex-col gap-8">
    <div>
        <flux:heading size="xl">Dashboard Mitra</flux:heading>
        <flux:text class="mt-1">Pantau request masuk dan pekerjaan yang sedang berjalan.</flux:text>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat-card label="Request Masuk" :value="$openRequestsCount" icon="inbox" />
        <x-ui.stat-card label="Job Aktif" :value="$activeJobsCount" icon="clipboard-document-list" />
        <x-ui.stat-card label="Layanan Ditawarkan" :value="$offeredServicesCount" icon="wrench-screwdriver" />
        <x-ui.stat-card
            label="Rating"
            :value="$ratingAverage === null ? '—' : number_format($ratingAverage, 1, ',', '.')"
            :hint="$reviewsCount > 0 ? $reviewsCount.' ulasan' : 'Belum ada ulasan'"
            icon="star"
        />
    </div>

    @if (! $isProfileComplete)
        <div class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-start gap-3">
                <flux:icon name="exclamation-triangle" class="size-5 shrink-0 text-amber-500" />

                <div class="min-w-0">
                    <p class="text-sm font-medium text-zinc-900">Lengkapi profil mitra</p>
                    <p class="mt-0.5 text-sm text-zinc-600">
                        Tambahkan bio dan area layanan supaya pelanggan yakin memilihmu.
                    </p>
                </div>
            </div>

            <flux:button :href="route('partner.profile')" wire:navigate variant="filled" size="sm" class="self-start">
                Lengkapi profil
            </flux:button>
        </div>
    @elseif (! $isAvailable)
        <div class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:icon name="pause-circle" class="size-5 shrink-0 text-zinc-400" />

            <p class="text-sm text-zinc-600">
                Kamu sedang tidak menerima job baru, jadi request baru tidak akan masuk.
            </p>

            <flux:button :href="route('partner.profile')" wire:navigate variant="ghost" size="sm" class="ms-auto shrink-0">
                Aktifkan
            </flux:button>
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">Job terdekat</flux:heading>

            @if ($activeJobsCount > 0)
                <flux:link :href="route('partner.jobs.index')" wire:navigate class="text-sm">
                    Lihat semua job
                </flux:link>
            @endif
        </div>

        @if ($upcomingJobs->isEmpty())
            <x-ui.empty-state
                icon="clipboard-document-list"
                title="Belum ada job aktif"
                description="Job yang kamu ambil atau menangkan akan tampil di sini."
            />
        @else
            <div class="flex flex-col gap-3">
                @foreach ($upcomingJobs as $job)
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
                            </div>
                        </flux:card>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">Request terbaru</flux:heading>

            @if ($openRequestsCount > 0)
                <flux:link :href="route('partner.requests.index')" wire:navigate class="text-sm">
                    Lihat semua request
                </flux:link>
            @endif
        </div>

        @if ($offeredServicesCount === 0)
            <x-ui.empty-state
                icon="wrench-screwdriver"
                title="Pilih layanan dulu"
                description="Request dari pelanggan hanya masuk untuk jasa yang kamu tawarkan."
            >
                <flux:button :href="route('partner.services.index')" wire:navigate variant="primary">
                    Pilih layanan
                </flux:button>
            </x-ui.empty-state>
        @elseif ($recentRequests->isEmpty())
            <x-ui.empty-state
                icon="inbox"
                title="Belum ada request masuk"
                description="Request baru dari pelanggan akan muncul di sini."
            />
        @else
            <div class="flex flex-col gap-3">
                @foreach ($recentRequests as $item)
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

                        <div class="flex justify-end">
                            <flux:button
                                size="sm"
                                variant="primary"
                                icon="arrow-right"
                                :href="route('partner.requests.show', $item)"
                                wire:navigate
                            >
                                {{ $item->mode->isDirect() ? 'Lihat & ambil job' : 'Kirim penawaran' }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    </div>
</div>
