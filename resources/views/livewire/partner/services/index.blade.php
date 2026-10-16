<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">Layanan Saya</flux:heading>
            <flux:text class="mt-1">
                Pilih jasa yang kamu kerjakan, lalu atur harganya. Sudah {{ $this->offeredCount }} jasa dipilih.
            </flux:text>
        </div>

        @if ($this->categories->isNotEmpty())
            <flux:button variant="primary" icon="check" wire:click="save" wire:loading.attr="disabled">
                Simpan
            </flux:button>
        @endif
    </div>

    @if ($this->categories->isEmpty())
        <x-ui.empty-state
            icon="wrench-screwdriver"
            title="Belum ada jasa yang bisa dipilih"
            description="Admin belum menambahkan jasa aktif. Coba lagi nanti."
        />
    @else
        <div class="flex flex-col gap-6">
            @foreach ($this->categories as $category)
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2">
                        <flux:icon :name="$category->icon ?? 'squares-2x2'" class="size-4 text-zinc-400" />
                        <flux:heading size="lg">{{ $category->name }}</flux:heading>
                    </div>

                    <div class="flex flex-col gap-3">
                        @foreach ($category->services as $service)
                            @php $row = $selection[(string) $service->id] ?? ['offered' => false]; @endphp

                            <flux:card class="flex flex-col gap-4" wire:key="service-{{ $service->id }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-zinc-900">{{ $service->name }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">
                                            Harga dasar:
                                            <x-ui.price :amount="$service->base_price" :unit="$service->price_unit->label()" />
                                        </p>
                                    </div>

                                    <flux:switch
                                        wire:model.live="selection.{{ $service->id }}.offered"
                                        aria-label="Sediakan {{ $service->name }}"
                                    />
                                </div>

                                @if ($row['offered'])
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <flux:input
                                            wire:model="selection.{{ $service->id }}.customPrice"
                                            type="number"
                                            min="0"
                                            label="Harga saya (Rp)"
                                            placeholder="mis. 150000"
                                            description="Kosongkan untuk memakai harga dasar."
                                        />

                                        <flux:input
                                            wire:model="selection.{{ $service->id }}.note"
                                            label="Catatan"
                                            placeholder="mis. termasuk isi freon"
                                            description="Tampil sebagai info tambahan bagimu."
                                        />
                                    </div>
                                @endif
                            </flux:card>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <flux:button variant="primary" icon="check" wire:click="save" wire:loading.attr="disabled">
                    Simpan layanan
                </flux:button>
            </div>
        </div>
    @endif
</div>
