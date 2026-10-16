<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Profil Saya</flux:heading>
        <flux:text class="mt-1">Atur data kontak supaya mitra mudah menghubungimu.</flux:text>
    </div>

    <flux:card class="flex items-center gap-4">
        <flux:avatar :name="$name" size="lg" color="teal" />

        <div class="min-w-0">
            <p class="truncate font-medium text-zinc-900">{{ $name }}</p>
            <p class="truncate text-sm text-zinc-500">{{ auth()->user()->email }}</p>
        </div>
    </flux:card>

    <div class="grid grid-cols-3 gap-3">
        <x-ui.stat-card label="Total Request" :value="$requestCount" icon="clipboard-document-list" />
        <x-ui.stat-card label="Selesai" :value="$completedCount" icon="check-badge" />
        <x-ui.stat-card label="Ulasan" :value="$reviewCount" icon="star" />
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input wire:model="name" label="Nama" required />
        <flux:input wire:model="phone" label="Nomor WhatsApp" placeholder="08xxxxxxxxxx" required />

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check">
                Simpan profil
            </flux:button>
        </div>
    </form>

    <form wire:submit="updatePassword" class="flex flex-col gap-6 border-t border-zinc-200 pt-6">
        <div>
            <flux:heading size="lg">Ubah password</flux:heading>
            <flux:text class="mt-1">Gunakan minimal 8 karakter yang tidak mudah ditebak.</flux:text>
        </div>

        <flux:input wire:model="currentPassword" type="password" label="Password saat ini" required />
        <flux:input wire:model="password" type="password" label="Password baru" required />
        <flux:input wire:model="password_confirmation" type="password" label="Ulangi password baru" required />

        <div class="flex justify-end">
            <flux:button type="submit" variant="filled" icon="key">
                Perbarui password
            </flux:button>
        </div>
    </form>
</div>
