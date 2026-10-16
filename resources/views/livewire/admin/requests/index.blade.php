<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Monitor Request</flux:heading>
        <flux:text class="mt-1">Semua request pelanggan beserta mitra yang menanganinya.</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Cari judul, pelanggan, atau jasa..."
            label="Cari"
        />

        <flux:select wire:model.live="status" label="Status">
            <flux:select.option value="">Semua status</flux:select.option>
            @foreach ($statuses as $option)
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->requests->isEmpty())
        <x-ui.empty-state
            icon="clipboard-document-list"
            title="Belum ada request"
            description="Request pelanggan akan tampil di sini untuk dipantau."
        />
    @else
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Request</flux:table.column>
                    <flux:table.column>Jasa</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Mitra</flux:table.column>
                    <flux:table.column align="end">Nilai</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->requests as $item)
                        <flux:table.row :key="$item->id">
                            <flux:table.cell variant="strong" class="whitespace-normal">
                                <div class="truncate">{{ $item->title }}</div>
                                <div class="truncate text-xs font-normal text-zinc-400">
                                    {{ $item->customer->name }} &middot;
                                    {{ $item->created_at->translatedFormat('d M Y') }} &middot;
                                    {{ $item->offers_count }} penawaran
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-normal">
                                <div class="truncate">{{ $item->service->name }}</div>
                                <div class="truncate text-xs text-zinc-400">{{ $item->mode->label() }}</div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-ui.status-pill :status="$item->status" />
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-normal">
                                @if ($item->partner)
                                    {{ $item->partner->user->name }}
                                @else
                                    <span class="text-zinc-400">Belum ada</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <x-ui.price
                                    :amount="$item->agreed_price ?? $item->service->base_price"
                                    :unit="$item->service->price_unit->label()"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @if ($this->requests->hasPages())
                <div class="mt-4">
                    <flux:pagination :paginator="$this->requests" />
                </div>
            @endif
        </div>
    @endif
</div>
