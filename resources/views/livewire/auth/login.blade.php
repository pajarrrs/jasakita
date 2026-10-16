<div>
    <flux:heading size="xl">Masuk</flux:heading>
    <flux:text class="mt-2">Masuk untuk memesan jasa atau mengelola pekerjaanmu.</flux:text>

    <form wire:submit="login" class="mt-6 space-y-6">
        <flux:input
            wire:model="email"
            label="Email"
            type="email"
            autocomplete="email"
            placeholder="nama@email.com"
            required
            autofocus
        />

        <flux:input
            wire:model="password"
            label="Password"
            type="password"
            autocomplete="current-password"
            placeholder="Password"
            required
            viewable
        />

        <flux:checkbox wire:model="remember" label="Ingat saya" />

        <flux:button type="submit" variant="primary" class="w-full">
            Masuk
        </flux:button>
    </form>

    <flux:text class="mt-6 text-center">
        Belum punya akun?
        <flux:link :href="route('register')" wire:navigate>Daftar sekarang</flux:link>
    </flux:text>
</div>
