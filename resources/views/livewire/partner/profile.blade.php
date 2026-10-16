<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Profil Mitra</flux:heading>
        <flux:text class="mt-1">Lengkapi data diri supaya pelanggan yakin memakai jasamu.</flux:text>
    </div>

    <flux:card class="flex items-center gap-4">
        <flux:avatar :name="$name" size="lg" color="teal" />

        <div class="min-w-0">
            <p class="truncate font-medium text-zinc-900">{{ $name }}</p>
            <p class="text-sm text-zinc-500">{{ $offeredServicesCount }} layanan ditawarkan</p>

            <x-ui.rating-stars class="mt-1" :rating="$ratingAverage" :count="$reviewsCount" />
        </div>

        <flux:badge :color="$isAvailable ? 'emerald' : 'zinc'" size="sm" class="ms-auto shrink-0">
            {{ $isAvailable ? 'Siap menerima job' : 'Sedang tidak menerima job' }}
        </flux:badge>
    </flux:card>

    <flux:card class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500">
                <flux:icon name="shield-check" class="size-5" />
            </span>

            <div class="min-w-0">
                <p class="text-sm font-medium text-zinc-900">Verifikasi identitas</p>
                <p class="text-xs text-zinc-500">
                    @if ($verificationStatus === null)
                        Belum diajukan. Verifikasi supaya dapat badge terverifikasi.
                    @else
                        {{ $verificationStatus->description() }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($verificationStatus !== null)
                <flux:badge :color="$verificationStatus->color()" :icon="$verificationStatus->icon()" size="sm">
                    {{ $verificationStatus->label() }}
                </flux:badge>
            @endif

            @if ($verificationStatus === null || $verificationStatus->isRejected())
                <flux:button :href="route('partner.verification')" size="sm" variant="primary" icon="arrow-right">
                    {{ $verificationStatus === null ? 'Verifikasi sekarang' : 'Kirim ulang' }}
                </flux:button>
            @endif
        </div>
    </flux:card>

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input
            wire:model="name"
            label="Nama"
            description="Nama ini yang dilihat pelanggan."
            required
        />

        <flux:input
            wire:model="phone"
            label="Nomor WhatsApp"
            placeholder="08xxxxxxxxxx"
            description="Dipakai admin untuk menghubungi kamu."
            required
        />

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input
                wire:model="city"
                label="Kota / area layanan"
                placeholder="mis. Bandung"
            />

            <flux:input
                wire:model="yearsOfExperience"
                type="number"
                label="Pengalaman (tahun)"
                min="0"
                max="80"
                placeholder="mis. 5"
            />
        </div>

        <flux:textarea
            wire:model="bio"
            label="Tentang saya"
            rows="4"
            placeholder="Ceritakan keahlian, alat yang dimiliki, dan jam operasionalmu."
        />

        <flux:switch
            wire:model="isAvailable"
            label="Siap menerima job"
            description="Nonaktifkan bila sedang penuh atau libur."
        />

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check">
                Simpan profil
            </flux:button>
        </div>
    </form>

    @if ($reviewsCount > 0)
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">Ulasan pelanggan</flux:heading>

            <div class="flex flex-col gap-3">
                @foreach ($reviews as $review)
                    <flux:card class="flex flex-col gap-2" wire:key="review-{{ $review->id }}">
                        <div class="flex items-center justify-between gap-3">
                            <x-ui.rating-stars :rating="$review->rating" />

                            <span class="text-xs text-zinc-400">
                                {{ $review->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>

                        @if ($review->comment)
                            <p class="text-sm leading-relaxed text-zinc-600">{{ $review->comment }}</p>
                        @endif

                        <p class="text-xs text-zinc-500">{{ $review->customer->name }}</p>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @endif
</div>
