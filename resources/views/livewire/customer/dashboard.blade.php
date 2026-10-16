<div class="flex flex-col gap-8">
    <div>
        <flux:heading size="xl">Halo, {{ auth()->user()->name }}</flux:heading>
        <flux:text class="mt-1">Jasa apa yang kamu butuhkan hari ini?</flux:text>
    </div>

    <a href="{{ route('customer.categories.index') }}" wire:navigate class="group">
        <flux:card class="flex items-center justify-between gap-4 transition group-hover:border-teal-300">
            <div class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <flux:icon name="magnifying-glass" class="size-5" />
                </span>

                <div>
                    <p class="text-sm font-medium text-zinc-900">Cari jasa</p>
                    <p class="text-xs text-zinc-500">Servis AC, permak, foto produk, dan lainnya</p>
                </div>
            </div>

            <flux:icon name="chevron-right" class="size-4 shrink-0 text-zinc-300" />
        </flux:card>
    </a>

    <div class="grid grid-cols-2 gap-3">
        <x-ui.stat-card label="Request Aktif" :value="$activeRequestCount" icon="clipboard-document-list" />
        <x-ui.stat-card label="Selesai" :value="$completedRequestCount" icon="check-badge" />
    </div>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">Request terbaru</flux:heading>

            <flux:link :href="route('customer.requests.index')" wire:navigate class="text-sm">
                Lihat semua
            </flux:link>
        </div>

        @if ($recentRequests->isEmpty())
            <x-ui.empty-state
                icon="clipboard-document-list"
                title="Belum ada request"
                description="Mulai dengan memilih jasa dari katalog, lalu kirim request pertamamu."
            >
                <flux:button :href="route('customer.categories.index')" wire:navigate variant="primary">
                    Jelajahi jasa
                </flux:button>
            </x-ui.empty-state>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($recentRequests as $item)
                    <x-ui.request-card :request="$item" />
                @endforeach
            </div>
        @endif
    </div>

    @if ($categories->isEmpty())
        <x-ui.empty-state
            icon="squares-2x2"
            title="Belum ada kategori jasa"
            description="Kategori jasa akan muncul di sini setelah ditambahkan oleh admin."
        />
    @else
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">Kategori populer</flux:heading>

                <flux:link :href="route('customer.categories.index')" wire:navigate class="text-sm">
                    Lihat semua
                </flux:link>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('customer.categories.show', $category) }}"
                        wire:navigate
                        wire:key="category-{{ $category->id }}"
                        class="group"
                    >
                        <flux:card class="flex h-full flex-col gap-2 transition group-hover:border-teal-300">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                                <flux:icon :name="$category->icon ?? 'squares-2x2'" class="size-5" />
                            </span>

                            <span class="text-sm font-medium text-zinc-900">{{ $category->name }}</span>
                            <span class="text-xs text-zinc-500">{{ $category->services_count }} layanan</span>
                        </flux:card>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
