<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">Daftar Jasa</flux:heading>
            <flux:text class="mt-1">Jasa yang bisa dipesan pelanggan.</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">
            Tambah Jasa
        </flux:button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Cari nama jasa..."
            label="Cari"
        />

        <flux:select wire:model.live="categoryFilter" label="Kategori">
            <flux:select.option value="">Semua kategori</flux:select.option>
            @foreach ($this->categories as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->services->isEmpty())
        <x-ui.empty-state
            icon="wrench-screwdriver"
            title="Belum ada jasa"
            description="Tambahkan jasa supaya pelanggan punya pilihan yang bisa dipesan."
        >
            <flux:button variant="primary" icon="plus" wire:click="create">
                Tambah Jasa
            </flux:button>
        </x-ui.empty-state>
    @else
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Jasa</flux:table.column>
                    <flux:table.column>Kategori</flux:table.column>
                    <flux:table.column>Harga Dasar</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->services as $service)
                        <flux:table.row :key="$service->id">
                            <flux:table.cell variant="strong" class="whitespace-normal">
                                <div class="truncate">{{ $service->name }}</div>
                                <div class="truncate text-xs font-normal text-zinc-400">
                                    {{ $service->duration_estimate ?? 'Durasi menyusul' }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $service->category->name }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-ui.price :amount="$service->base_price" :unit="$service->price_unit->label()" />
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$service->is_active ? 'emerald' : 'zinc'" size="sm">
                                    {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-1">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        wire:click="edit({{ $service->id }})"
                                        aria-label="Ubah {{ $service->name }}"
                                    />

                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="delete({{ $service->id }})"
                                        wire:confirm="Hapus jasa &quot;{{ $service->name }}&quot;?"
                                        aria-label="Hapus {{ $service->name }}"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @if ($this->services->hasPages())
                <div class="mt-4">
                    <flux:pagination :paginator="$this->services" />
                </div>
            @endif
        </div>
    @endif

    <flux:modal wire:model.self="showForm" class="md:w-lg">
        <form wire:submit="save" class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Ubah Jasa' : 'Tambah Jasa' }}
                </flux:heading>
                <flux:text class="mt-1">Harga dasar adalah patokan awal; mitra tetap bisa mengirim penawaran.</flux:text>
            </div>

            <flux:input
                wire:model="name"
                label="Nama jasa"
                placeholder="mis. Servis AC"
                required
                autofocus
            />

            <flux:select wire:model="categoryId" label="Kategori" required>
                <flux:select.option value="">Pilih kategori</flux:select.option>
                @foreach ($this->categories as $category)
                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea
                wire:model="description"
                label="Deskripsi"
                rows="3"
                placeholder="Jelaskan cakupan pekerjaan dan hal yang perlu disiapkan pelanggan"
            />

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input
                    wire:model="basePrice"
                    type="number"
                    label="Harga dasar (Rp)"
                    min="0"
                    placeholder="mis. 150000"
                    description="Kosongkan bila harga menyesuaikan kondisi."
                />

                <flux:select wire:model="priceUnit" label="Satuan harga">
                    @foreach ($priceUnits as $unit)
                        <flux:select.option value="{{ $unit->value }}">{{ ucfirst($unit->label()) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input
                wire:model="durationEstimate"
                label="Perkiraan durasi"
                placeholder="mis. 1-2 jam"
            />

            <flux:switch
                wire:model="isActive"
                label="Aktif"
                description="Jasa nonaktif disembunyikan dari pelanggan."
            />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Simpan perubahan' : 'Tambah jasa' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
