<div class="flex flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('partner.requests.index')" wire:navigate>
            Request Masuk
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>#{{ $serviceRequest->id }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <flux:heading size="xl">{{ $serviceRequest->title }}</flux:heading>
            <flux:text class="mt-1">
                {{ $serviceRequest->service->name }} &middot;
                {{ $serviceRequest->created_at->diffForHumans() }}
            </flux:text>
        </div>

        <flux:badge :color="$serviceRequest->mode->color()" size="sm">{{ $serviceRequest->mode->label() }}</flux:badge>
    </div>

    @if (! $this->profile->is_available)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Aktifkan status <span class="font-medium">siap menerima job</span> dulu di halaman
            <a href="{{ route('partner.profile') }}" wire:navigate class="font-medium underline">profil</a>
            sebelum menanggapi request.
        </div>
    @endif

    <flux:card class="flex flex-col gap-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs text-zinc-500">Area pelanggan</p>
                <p class="text-sm font-medium text-zinc-900">{{ $serviceRequest->city ?? 'Belum diisi' }}</p>
            </div>

            <div>
                <p class="text-xs text-zinc-500">Waktu yang diinginkan</p>
                <p class="text-sm font-medium text-zinc-900">
                    {{ $serviceRequest->scheduled_at?->translatedFormat('d F Y, H:i') ?? 'Belum ditentukan' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-zinc-500">Patokan harga katalog</p>
                <x-ui.price
                    class="text-sm font-medium text-zinc-900"
                    :amount="$serviceRequest->service->base_price"
                    :unit="$serviceRequest->service->price_unit->label()"
                />
            </div>

            <div>
                <p class="text-xs text-zinc-500">Harga saya untuk jasa ini</p>
                <x-ui.price
                    class="text-sm font-medium text-zinc-900"
                    :amount="$this->profile->effectivePriceFor($serviceRequest->service)"
                    :unit="$serviceRequest->service->price_unit->label()"
                />
            </div>
        </div>

        <div>
            <p class="text-xs text-zinc-500">Detail kebutuhan</p>
            <p class="mt-1 text-sm leading-relaxed text-zinc-600">{{ $serviceRequest->description }}</p>
        </div>

        <p class="text-xs text-zinc-400">
            Alamat lengkap dan nomor pelanggan terbuka setelah kamu menjadi mitra pengerjaan.
        </p>
    </flux:card>

    @if ($serviceRequest->mode->isDirect())
        <flux:card class="flex flex-wrap items-center gap-4">
            <div class="min-w-0">
                <p class="text-sm font-medium text-zinc-900">Pesan langsung: siapa cepat, dia dapat</p>
                <p class="mt-0.5 text-sm text-zinc-500">
                    Ambil job ini dan kamu akan mengerjakan dengan harga
                    Rp {{ number_format((float) $this->profile->effectivePriceFor($serviceRequest->service), 0, ',', '.') }}.
                </p>
            </div>

            <flux:button
                variant="primary"
                icon="hand-raised"
                class="ms-auto shrink-0"
                :disabled="! $this->profile->is_available"
                wire:click="takeJobNow"
                wire:confirm="Ambil job ini sekarang?"
            >
                Ambil job
            </flux:button>
        </flux:card>
    @else
        <form wire:submit="sendOffer" class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ $this->myOffer ? 'Perbarui penawaran' : 'Kirim penawaran' }}</flux:heading>
                <flux:text class="mt-1">
                    {{ $this->myOffer ? 'Penawaran baru akan menggantikan penawaran sebelumnya.' : 'Ajukan harga terbaikmu untuk request ini.' }}
                </flux:text>
            </div>

            <flux:input
                wire:model="price"
                type="number"
                min="1000"
                label="Harga penawaran (Rp)"
                placeholder="mis. 175000"
                required
            />

            <flux:input
                wire:model="durationEstimate"
                label="Perkiraan durasi"
                placeholder="mis. 1-2 jam"
            />

            <flux:textarea
                wire:model="message"
                label="Pesan untuk pelanggan"
                rows="3"
                placeholder="Sebutkan alat yang dibawa, garansi, atau catatan penting lain."
            />

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="paper-airplane" :disabled="! $this->profile->is_available">
                    {{ $this->myOffer ? 'Perbarui penawaran' : 'Kirim penawaran' }}
                </flux:button>
            </div>
        </form>
    @endif

    @if ($this->myOffer)
        <flux:card class="flex flex-wrap items-center gap-x-6 gap-y-2">
            <div>
                <p class="text-xs text-zinc-500">Status penawaranmu</p>
                <flux:badge :color="$this->myOffer->status->color()" size="sm">
                    {{ $this->myOffer->status->label() }}
                </flux:badge>
            </div>

            <div>
                <p class="text-xs text-zinc-500">Harga ditawarkan</p>
                <x-ui.price
                    class="text-sm font-medium text-zinc-900"
                    :amount="$this->myOffer->price"
                    :unit="$serviceRequest->service->price_unit->label()"
                />
            </div>

            @if ($this->myOffer->duration_estimate)
                <div>
                    <p class="text-xs text-zinc-500">Durasi</p>
                    <p class="text-sm font-medium text-zinc-900">{{ $this->myOffer->duration_estimate }}</p>
                </div>
            @endif

            <p class="text-xs text-zinc-400">
                Dikirim {{ $this->myOffer->created_at->diffForHumans() }}
            </p>
        </flux:card>
    @endif
</div>
