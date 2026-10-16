<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">Request Saya</flux:heading>
            <flux:text class="mt-1">Pantau status request dan penawaran dari mitra.</flux:text>
        </div>

        <flux:button :href="route('customer.categories.index')" wire:navigate variant="primary" icon="plus">
            Request baru
        </flux:button>
    </div>

    <flux:select wire:model.live="status" label="Filter status">
        <flux:select.option value="">Semua status</flux:select.option>
        @foreach ($statuses as $option)
            <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->requests->isEmpty())
        <x-ui.empty-state
            icon="clipboard-document-list"
            title="Belum ada request"
            description="Request yang kamu buat akan muncul di sini beserta penawaran dari mitra."
        >
            <flux:button :href="route('customer.categories.index')" wire:navigate variant="primary">
                Jelajahi jasa
            </flux:button>
        </x-ui.empty-state>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($this->requests as $item)
                <x-ui.request-card :request="$item" />
            @endforeach
        </div>

        @if ($this->requests->hasPages())
            <flux:pagination :paginator="$this->requests" />
        @endif
    @endif
</div>
