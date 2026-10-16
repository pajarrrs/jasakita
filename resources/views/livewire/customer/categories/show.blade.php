<div class="flex flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('customer.categories.index')" wire:navigate>
            Kategori
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $category->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex items-start gap-3">
        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
            <flux:icon :name="$category->icon ?? 'squares-2x2'" class="size-6" />
        </span>

        <div class="min-w-0">
            <flux:heading size="xl">{{ $category->name }}</flux:heading>

            @if ($category->description)
                <flux:text class="mt-1">{{ $category->description }}</flux:text>
            @endif
        </div>
    </div>

    @if ($this->services->isEmpty())
        <x-ui.empty-state
            icon="wrench-screwdriver"
            title="Belum ada jasa di kategori ini"
            description="Coba lihat kategori lain, atau kembali lagi nanti."
        >
            <flux:button :href="route('customer.categories.index')" wire:navigate variant="filled">
                Lihat kategori lain
            </flux:button>
        </x-ui.empty-state>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($this->services as $service)
                <a
                    href="{{ route('customer.services.show', $service) }}"
                    wire:navigate
                    wire:key="service-{{ $service->id }}"
                    class="group"
                >
                    <flux:card class="flex flex-col gap-2 transition group-hover:border-teal-300">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium text-zinc-900">{{ $service->name }}</p>

                            <x-ui.price
                                class="shrink-0 text-sm font-medium text-zinc-900"
                                :amount="$service->base_price"
                                :unit="$service->price_unit->label()"
                            />
                        </div>

                        @if ($service->description)
                            <p class="line-clamp-2 text-sm text-zinc-500">{{ $service->description }}</p>
                        @endif

                        <p class="flex items-center gap-1.5 text-xs text-zinc-400">
                            <flux:icon name="clock" class="size-4" />
                            {{ $service->duration_estimate ?? 'Durasi menyesuaikan' }}
                        </p>
                    </flux:card>
                </a>
            @endforeach
        </div>
    @endif
</div>
