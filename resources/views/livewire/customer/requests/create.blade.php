<div class="flex flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('customer.categories.index')" wire:navigate>
            Kategori
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('customer.categories.show', $service->category)" wire:navigate>
            {{ $service->category->name }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('customer.services.show', $service)" wire:navigate>
            {{ $service->name }}
        </flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl">Ajukan Request</flux:heading>
        <flux:text class="mt-1">Isi detail kebutuhanmu supaya mitra bisa memberi harga yang tepat.</flux:text>
    </div>

    <flux:card class="flex items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-zinc-900">{{ $service->name }}</p>
            <p class="text-xs text-zinc-500">Patokan harga dari katalog</p>
        </div>

        <x-ui.price
            class="shrink-0 text-sm font-medium"
            :amount="$service->base_price"
            :unit="$service->price_unit->label()"
        />
    </flux:card>

    <form wire:submit="createRequest" class="flex flex-col gap-6">
        <flux:select wire:model.live="mode" label="Cara pesan">
            @foreach ($modes as $option)
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="rounded-xl bg-teal-50 p-3 text-sm text-teal-800">
            {{ $this->selectedMode->description() }}
        </div>

        <flux:input
            wire:model="title"
            label="Judul request"
            placeholder="mis. Servis AC kamar tidur"
            required
        />

        <flux:textarea
            wire:model="description"
            label="Detail kebutuhan"
            rows="4"
            placeholder="Jelaskan kondisi, jumlah unit, dan hal yang perlu disiapkan mitra."
            description="Minimal 10 karakter."
            required
        />

        <flux:input
            wire:model="address"
            label="Alamat lengkap"
            placeholder="Nama jalan, nomor rumah, patokan"
            required
        />

        <flux:input
            wire:model="city"
            label="Kota / kecamatan"
            placeholder="mis. Bandung"
        />

        <flux:input
            wire:model="scheduledAt"
            type="datetime-local"
            label="Waktu yang diinginkan (opsional)"
            description="Boleh dikosongkan bila ingin berdiskusi jadwal dengan mitra."
        />

        <div class="flex flex-wrap justify-end gap-2">
            <flux:button :href="route('customer.services.show', $service)" wire:navigate variant="filled">
                Batal
            </flux:button>

            <flux:button type="submit" variant="primary" icon="paper-airplane">
                Kirim request
            </flux:button>
        </div>
    </form>
</div>
