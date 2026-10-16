@php
    $requestRouteExists = \Illuminate\Support\Facades\Route::has('customer.requests.create');
@endphp

<div class="flex flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('customer.categories.index')" wire:navigate>
            Kategori
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('customer.categories.show', $service->category)" wire:navigate>
            {{ $service->category->name }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $service->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <flux:badge color="teal" size="sm">{{ $service->category->name }}</flux:badge>

            <flux:badge size="sm">
                <flux:icon name="clock" class="size-3.5" />
                {{ $service->duration_estimate ?? 'Durasi menyesuaikan' }}
            </flux:badge>
        </div>

        <flux:heading size="xl">{{ $service->name }}</flux:heading>
    </div>

    <flux:card class="flex flex-col gap-1">
        <span class="text-sm text-zinc-500">Harga dasar</span>
        <x-ui.price
            class="text-2xl font-semibold text-zinc-900"
            :amount="$service->base_price"
            :unit="$service->price_unit->label()"
        />
        <span class="text-xs text-zinc-400">Harga akhir bisa berbeda sesuai kondisi di lokasi.</span>
    </flux:card>

    @if ($service->description)
        <div class="flex flex-col gap-2">
            <flux:heading size="lg">Tentang jasa ini</flux:heading>
            <p class="text-sm leading-relaxed text-zinc-600">{{ $service->description }}</p>
        </div>
    @endif

    @if ($requestRouteExists)
        <div class="flex flex-col gap-3 sm:flex-row">
            <flux:button
                :href="route('customer.requests.create', ['service' => $service, 'mode' => 'quote'])"
                wire:navigate
                variant="primary"
                class="sm:flex-1"
            >
                Minta Penawaran
            </flux:button>

            <flux:button
                :href="route('customer.requests.create', ['service' => $service, 'mode' => 'direct'])"
                wire:navigate
                variant="filled"
                class="sm:flex-1"
            >
                Pesan Langsung
            </flux:button>
        </div>
    @else
        <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4">
            <p class="text-sm font-medium text-zinc-900">Cara memesan</p>

            <ol class="flex flex-col gap-2 text-sm text-zinc-500">
                <li class="flex gap-2">
                    <span class="font-medium text-zinc-400">1.</span>
                    Kirim permintaan dengan detail kebutuhan dan lokasimu.
                </li>
                <li class="flex gap-2">
                    <span class="font-medium text-zinc-400">2.</span>
                    Mitra terdekat mengirim penawaran harga.
                </li>
                <li class="flex gap-2">
                    <span class="font-medium text-zinc-400">3.</span>
                    Pilih penawaran terbaik, lalu tentukan jadwal pengerjaan.
                </li>
            </ol>
        </div>
    @endif
</div>
