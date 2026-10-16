<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Jelajahi Jasa</flux:heading>
        <flux:text class="mt-1">Pilih kategori, lalu lihat jasa yang tersedia.</flux:text>
    </div>

    <flux:input
        wire:model.live.debounce.300ms="search"
        icon="magnifying-glass"
        placeholder="Cari jasa, mis. servis AC"
        aria-label="Cari jasa"
    />

    @if (trim($search) !== '')
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">Hasil pencarian</flux:heading>

            @if ($this->matchingServices->isEmpty())
                <x-ui.empty-state
                    icon="magnifying-glass"
                    title="Jasa tidak ditemukan"
                    description="Coba kata kunci lain, atau telusuri kategori di bawah."
                />
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($this->matchingServices as $service)
                        <a
                            href="{{ route('customer.services.show', $service) }}"
                            wire:navigate
                            wire:key="service-{{ $service->id }}"
                            class="group"
                        >
                            <flux:card class="flex items-center justify-between gap-4 transition group-hover:border-teal-300">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900">{{ $service->name }}</p>
                                    <p class="truncate text-xs text-zinc-500">
                                        {{ $service->category->name }} &middot; {{ $service->duration_estimate ?? 'durasi menyusul' }}
                                    </p>
                                </div>

                                <flux:icon name="chevron-right" class="size-4 shrink-0 text-zinc-300" />
                            </flux:card>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <flux:heading size="lg">Kategori</flux:heading>

        @if ($this->categories->isEmpty())
            <x-ui.empty-state
                icon="squares-2x2"
                title="Belum ada kategori jasa"
                description="Kategori akan muncul di sini setelah ditambahkan oleh admin."
            />
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($this->categories as $category)
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
        @endif
    </div>
</div>
