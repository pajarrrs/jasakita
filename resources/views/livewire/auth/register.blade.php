<div>
    <flux:heading size="xl">Daftar</flux:heading>
    <flux:text class="mt-2">Buat akun untuk memesan jasa atau menawarkan jasamu.</flux:text>

    <form wire:submit="register" class="mt-6 space-y-6">
        <flux:radio.group wire:model="role" variant="cards" label="Daftar sebagai">
            <flux:radio value="customer" label="Pelanggan" description="Saya ingin mencari dan memesan jasa." />
            <flux:radio value="partner" label="Mitra" description="Saya ingin menawarkan jasa saya." />
        </flux:radio.group>

        <flux:input wire:model="name" label="Nama lengkap" required autofocus />

        <flux:input wire:model="email" label="Email" type="email" autocomplete="email" placeholder="nama@email.com" required />

        <flux:input wire:model="phone" label="Nomor HP" type="tel" autocomplete="tel" placeholder="08xxxxxxxxxx" required />

        <flux:input
            wire:model="password"
            label="Password"
            type="password"
            autocomplete="new-password"
            placeholder="Minimal 8 karakter"
            required
            viewable
        />

        <flux:input
            wire:model="password_confirmation"
            label="Ulangi password"
            type="password"
            autocomplete="new-password"
            placeholder="Ulangi password"
            required
        />

        <flux:button type="submit" variant="primary" class="w-full">
            Daftar
        </flux:button>
    </form>

    <flux:text class="mt-6 text-center">
        Sudah punya akun?
        <flux:link :href="route('login')" wire:navigate>Masuk</flux:link>
    </flux:text>
</div>
