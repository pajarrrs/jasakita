<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-zinc-50 font-sans text-zinc-900 antialiased">
    <div class="flex min-h-dvh flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 flex items-center justify-center gap-2">
                <span class="flex size-9 items-center justify-center rounded-xl bg-teal-600 text-sm font-bold text-white">
                    JK
                </span>
                <span class="text-lg font-semibold">{{ config('app.name') }}</span>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>

    <flux:toast />
    @fluxScripts
</body>
</html>
