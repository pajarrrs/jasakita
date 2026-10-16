<div class="flex flex-col gap-8">
    <div>
        <flux:heading size="xl">Dashboard Admin</flux:heading>
        <flux:text class="mt-1">Ringkasan data aplikasi.</flux:text>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat-card label="Kategori Jasa" :value="$categoryCount" icon="squares-2x2" />
        <x-ui.stat-card label="Total Jasa" :value="$serviceCount" icon="wrench-screwdriver" />
        <x-ui.stat-card label="Pengguna" :value="$userCount" icon="users" />
        <x-ui.stat-card label="Mitra" :value="$partnerCount" icon="briefcase" />
        <x-ui.stat-card label="Total Request" :value="$requestCount" icon="clipboard-document-list" />
        <x-ui.stat-card label="Request Aktif" :value="$activeRequestCount" icon="clock" />
        <x-ui.stat-card label="Verifikasi Menunggu" :value="$pendingVerificationCount" icon="shield-check" />
    </div>

    @if ($pendingVerificationCount > 0)
        <flux:card class="flex flex-wrap items-center justify-between gap-3 border-amber-200 bg-amber-50">
            <div class="flex items-center gap-3">
                <flux:icon name="shield-check" class="size-5 shrink-0 text-amber-600" />

                <div>
                    <p class="text-sm font-medium text-amber-900">
                        {{ $pendingVerificationCount }} pengajuan verifikasi mitra menunggu review
                    </p>
                    <p class="text-xs text-amber-700">Periksa data identitas mitra untuk memberi badge terverifikasi.</p>
                </div>
            </div>

            <flux:button :href="route('admin.verifications.index')" size="sm" variant="filled" icon="arrow-right" wire:navigate>
                Periksa sekarang
            </flux:button>
        </flux:card>
    @endif

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">Request terbaru</flux:heading>

            <flux:link :href="route('admin.requests.index')" wire:navigate class="text-sm">
                Monitor semua request
            </flux:link>
        </div>

        @if ($recentRequests->isEmpty())
            <x-ui.empty-state
                icon="clipboard-document-list"
                title="Belum ada request"
                description="Request pelanggan akan tampil di sini untuk dipantau."
            />
        @else
            <div class="flex flex-col gap-3">
                @foreach ($recentRequests as $item)
                    <flux:card class="flex flex-col gap-3" wire:key="request-{{ $item->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900">{{ $item->title }}</p>
                                <p class="truncate text-xs text-zinc-500">
                                    {{ $item->customer->name }} &middot; {{ $item->service->name }}
                                    &middot; {{ $item->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <x-ui.status-pill :status="$item->status" class="shrink-0" />
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
                            <span class="flex items-center gap-1">
                                <flux:icon name="tag" class="size-3.5" />
                                {{ $item->mode->label() }}
                            </span>

                            <span class="flex items-center gap-1">
                                <flux:icon name="user" class="size-3.5" />
                                {{ $item->partner?->user->name ?? 'Mitra belum ada' }}
                            </span>

                            <span class="flex items-center gap-1">
                                <flux:icon name="banknotes" class="size-3.5" />
                                <x-ui.price
                                    :amount="$item->agreed_price ?? $item->service->base_price"
                                    :unit="$item->service->price_unit->label()"
                                />
                            </span>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    </div>
</div>
