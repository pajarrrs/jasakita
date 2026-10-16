<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">Kategori Jasa</flux:heading>
            <flux:text class="mt-1">Kelompok jasa yang tampil di halaman pelanggan.</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create">
            Tambah Kategori
        </flux:button>
    </div>

    @if ($this->categories->isEmpty())
        <x-ui.empty-state
            icon="squares-2x2"
            title="Belum ada kategori"
            description="Tambahkan kategori pertama supaya pelanggan bisa mulai menjelajah."
        >
            <flux:button variant="primary" icon="plus" wire:click="create">
                Tambah Kategori
            </flux:button>
        </x-ui.empty-state>
    @else
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kategori</flux:table.column>
                    <flux:table.column>Jasa</flux:table.column>
                    <flux:table.column>Urutan</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->categories as $category)
                        <flux:table.row :key="$category->id">
                            <flux:table.cell variant="strong" class="whitespace-normal">
                                <div class="flex items-center gap-2">
                                    <flux:icon :name="$category->icon ?? 'squares-2x2'" class="size-4 shrink-0 text-zinc-400" />

                                    <div class="min-w-0">
                                        <div class="truncate">{{ $category->name }}</div>
                                        <div class="truncate text-xs font-normal text-zinc-400">{{ $category->slug }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>{{ $category->services_count }}</flux:table.cell>

                            <flux:table.cell>{{ $category->sort_order }}</flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$category->is_active ? 'emerald' : 'zinc'" size="sm">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-1">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        wire:click="edit({{ $category->id }})"
                                        aria-label="Ubah {{ $category->name }}"
                                    />

                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="delete({{ $category->id }})"
                                        wire:confirm="Hapus kategori &quot;{{ $category->name }}&quot;?"
                                        aria-label="Hapus {{ $category->name }}"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @if ($this->categories->hasPages())
                <div class="mt-4">
                    <flux:pagination :paginator="$this->categories" />
                </div>
            @endif
        </div>
    @endif

    <flux:modal wire:model.self="showForm" class="md:w-lg">
        <form wire:submit="save" class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Ubah Kategori' : 'Tambah Kategori' }}
                </flux:heading>
                <flux:text class="mt-1">Kategori membantu pelanggan menemukan jasa yang tepat.</flux:text>
            </div>

            <flux:input
                wire:model="name"
                label="Nama kategori"
                placeholder="mis. Servis Elektronik & Pendingin"
                required
                autofocus
            />

            <flux:textarea
                wire:model="description"
                label="Deskripsi"
                rows="3"
                placeholder="Penjelasan singkat tentang kategori ini"
            />

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:select wire:model="icon" label="Ikon">
                    @foreach ($icons as $iconName)
                        <flux:select.option value="{{ $iconName }}">{{ $iconName }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="sortOrder"
                    type="number"
                    label="Urutan tampil"
                    min="0"
                    max="999"
                    description="Angka kecil tampil lebih dulu."
                />
            </div>

            <flux:switch
                wire:model="isActive"
                label="Aktif"
                description="Kategori nonaktif disembunyikan dari pelanggan."
            />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Simpan perubahan' : 'Tambah kategori' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
