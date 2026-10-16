@props([
    'area' => 'customer',
    'title' => null,
])

@php
    $user = auth()->user();

    $navigation = match ($area) {
        'partner' => [
            ['label' => 'Dashboard', 'route' => 'partner.dashboard', 'icon' => 'chart-bar', 'pattern' => 'partner.dashboard'],
            ['label' => 'Request Masuk', 'route' => 'partner.requests.index', 'icon' => 'inbox', 'pattern' => 'partner.requests.*'],
            ['label' => 'Job Saya', 'route' => 'partner.jobs.index', 'icon' => 'clipboard-document-list', 'pattern' => 'partner.jobs.*'],
            ['label' => 'Layanan', 'route' => 'partner.services.index', 'icon' => 'wrench-screwdriver', 'pattern' => 'partner.services.*'],
            ['label' => 'Verifikasi', 'route' => 'partner.verification', 'icon' => 'shield-check', 'pattern' => 'partner.verification'],
            ['label' => 'Profil', 'route' => 'partner.profile', 'icon' => 'user-circle', 'pattern' => 'partner.profile'],
        ],
        'admin' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'chart-bar', 'pattern' => 'admin.dashboard'],
            ['label' => 'Kategori', 'route' => 'admin.categories.index', 'icon' => 'squares-2x2', 'pattern' => 'admin.categories.*'],
            ['label' => 'Jasa', 'route' => 'admin.services.index', 'icon' => 'wrench-screwdriver', 'pattern' => 'admin.services.*'],
            ['label' => 'Pengguna', 'route' => 'admin.users.index', 'icon' => 'users', 'pattern' => 'admin.users.*'],
            ['label' => 'Verifikasi', 'route' => 'admin.verifications.index', 'icon' => 'shield-check', 'pattern' => 'admin.verifications.*'],
            ['label' => 'Request', 'route' => 'admin.requests.index', 'icon' => 'clipboard-document-list', 'pattern' => 'admin.requests.*'],
        ],
        default => [
            ['label' => 'Home', 'route' => 'customer.dashboard', 'icon' => 'home', 'pattern' => 'customer.dashboard'],
            ['label' => 'Kategori', 'route' => 'customer.categories.index', 'icon' => 'squares-2x2', 'pattern' => 'customer.categories.*'],
            ['label' => 'Request', 'route' => 'customer.requests.index', 'icon' => 'clipboard-document-list', 'pattern' => 'customer.requests.*'],
            ['label' => 'Profil', 'route' => 'customer.profile', 'icon' => 'user-circle', 'pattern' => 'customer.profile'],
        ],
    };

    $navigation = array_values(array_filter(
        $navigation,
        fn (array $item) => \Illuminate\Support\Facades\Route::has($item['route']),
    ));

    $mobileNavigation = array_slice($navigation, 0, 5);

    $profileRoute = match ($area) {
        'partner' => 'partner.profile',
        'admin' => 'admin.dashboard',
        default => 'customer.profile',
    };

    $profileRoute = \Illuminate\Support\Facades\Route::has($profileRoute) ? $profileRoute : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-zinc-50 font-sans text-zinc-900 antialiased">
    <div class="min-h-dvh lg:flex">
        {{-- Sidebar (desktop) --}}
        <aside class="hidden lg:sticky lg:top-0 lg:flex lg:h-dvh lg:w-64 lg:shrink-0 lg:flex-col lg:border-r lg:border-zinc-200 lg:bg-white">
            <div class="flex h-16 items-center gap-2 border-b border-zinc-200 px-5">
                <span class="flex size-8 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">
                    JK
                </span>
                <span class="text-sm font-semibold">{{ config('app.name') }}</span>

                @if ($user)
                    <div class="ms-auto">
                        <livewire:shared.notification-bell wire:key="notification-bell-desktop" />
                    </div>
                @endif
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                @foreach ($navigation as $item)
                    @php $isActive = request()->routeIs($item['pattern']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                            'bg-teal-50 text-teal-700' => $isActive,
                            'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' => ! $isActive,
                        ])
                    >
                        <flux:icon :name="$item['icon']" class="size-5" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            @if ($user)
                <div class="border-t border-zinc-200 p-3">
                    <flux:dropdown position="top" align="start">
                        <button type="button" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left hover:bg-zinc-100">
                            <flux:avatar :name="$user->name" size="sm" color="teal" />
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium">{{ $user->name }}</span>
                                <span class="block truncate text-xs text-zinc-500">{{ $user->role->label() }}</span>
                            </span>
                            <flux:icon name="chevron-up-down" class="size-4 text-zinc-400" />
                        </button>

                        <flux:menu>
                            @if ($profileRoute)
                                <flux:menu.item icon="user-circle" :href="route($profileRoute)" wire:navigate>
                                    Profil
                                </flux:menu.item>
                                <flux:menu.separator />
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-left-start-on-rectangle" class="w-full cursor-pointer">
                                    Keluar
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @endif
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top bar (mobile) --}}
            <header class="sticky top-0 z-30 flex h-14 items-center justify-between gap-3 border-b border-zinc-200 bg-white/90 px-4 backdrop-blur lg:hidden">
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold text-white">
                        JK
                    </span>
                    <span class="text-sm font-semibold">{{ config('app.name') }}</span>
                </div>

                @if ($user)
                    <div class="flex items-center gap-1">
                        <livewire:shared.notification-bell wire:key="notification-bell-mobile" />

                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" square>
                                <flux:avatar :name="$user->name" size="xs" color="teal" />
                            </flux:button>

                            <flux:menu>
                                <flux:menu.heading>{{ $user->name }}</flux:menu.heading>
                                <flux:menu.separator />
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-left-start-on-rectangle" class="w-full cursor-pointer">
                                        Keluar
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @endif
            </header>

            <main class="flex-1">
                <div class="mx-auto w-full max-w-5xl px-4 py-6 pb-28 lg:px-8 lg:py-8 lg:pb-12">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{-- Bottom navigation (mobile) --}}
    @if (count($mobileNavigation) > 0)
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden">
            <div class="mx-auto flex max-w-md items-stretch">
                @foreach ($mobileNavigation as $item)
                    @php $isActive = request()->routeIs($item['pattern']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        @class([
                            'flex flex-1 flex-col items-center gap-1 px-1 py-2 text-[11px] font-medium transition',
                            'text-teal-700' => $isActive,
                            'text-zinc-500' => ! $isActive,
                        ])
                    >
                        <flux:icon :name="$item['icon']" :variant="$isActive ? 'solid' : 'outline'" class="size-6" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    @endif

    <flux:toast />
    @fluxScripts
</body>
</html>
