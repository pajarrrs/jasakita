@php
    $isMitra = $app['slug'] === 'mitra';

    $accent = $isMitra
        ? ['icon' => 'bg-emerald-600', 'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'button' => 'bg-emerald-600 hover:bg-emerald-700', 'text' => 'text-emerald-700']
        : ['icon' => 'bg-teal-600', 'badge' => 'border-teal-200 bg-teal-50 text-teal-700', 'button' => 'bg-teal-600 hover:bg-teal-700', 'text' => 'text-teal-700'];
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Unduh {{ $app['name'] }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-white font-sans text-zinc-900 antialiased">
    <header class="border-b border-zinc-200/80">
        <div class="mx-auto flex h-16 max-w-3xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="flex size-8 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">
                    JK
                </span>
                <span class="text-sm font-semibold">{{ config('app.name') }}</span>
            </a>

            <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-500 transition hover:text-zinc-900">
                &larr; Kembali
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex size-14 items-center justify-center rounded-2xl {{ $accent['icon'] }} text-lg font-bold text-white">
                        {{ $isMitra ? 'M' : 'JK' }}
                    </span>

                    <div>
                        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{{ $app['name'] }}</h1>
                        <p class="mt-1 text-sm text-zinc-500">{{ $app['audience'] }} &middot; Android 8.0+</p>
                    </div>
                </div>

                <span class="rounded-full border px-3 py-1 text-xs font-medium {{ $accent['badge'] }}">
                    Versi {{ $app['version'] }}
                </span>
            </div>

            <p class="mt-5 text-sm leading-relaxed text-zinc-600">{{ $app['tagline'] }}</p>

            <ul class="mt-6 flex flex-col gap-3">
                @foreach ($app['highlights'] as $highlight)
                    <li class="flex gap-2.5 text-sm leading-relaxed text-zinc-600">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 size-4 shrink-0 {{ $accent['text'] }}">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                        </svg>
                        {{ $highlight }}
                    </li>
                @endforeach
            </ul>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a
                    href="{{ $downloadUrl }}"
                    download="{{ $app['filename'] }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition {{ $accent['button'] }}"
                >
                    <svg viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.2439 13.8533 7.8508 12 7.8508s-3.5902.3931-5.1367 1.0989L4.841 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3435-4.1021-2.6892-7.5743-6.1185-9.4396" />
                    </svg>
                    Unduh APK
                </a>

                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 px-6 py-3.5 text-sm font-semibold text-zinc-700 transition hover:border-teal-300 hover:text-teal-700"
                >
                    Atau buka versi web
                </a>
            </div>

            <p class="mt-4 text-xs text-zinc-500">
                @if ($downloadAvailable)
                    Berkas: {{ $app['filename'] }} &middot; {{ $fileSize }} &middot; tanpa Play Store, langsung pasang.
                @else
                    Berkas APK belum tersedia di server ini. Jalankan
                    <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[11px]">resources/android/build.sh</code>
                    lalu muat ulang halaman.
                @endif
            </p>
        </div>

        <div class="mt-8">
            <h2 class="text-sm font-semibold text-zinc-900">Cara memasang</h2>

            <ol class="mt-4 flex flex-col gap-4">
                @foreach ([
                    'Unduh berkas APK lewat tombol di atas.',
                    'Buka berkasnya, lalu izinkan pemasangan dari sumber tidak dikenal bila diminta.',
                    'Buka aplikasi, lalu masuk dengan akun '.($isMitra ? 'mitra' : 'pelanggan').'mu.',
                ] as $index => $step)
                    <li class="flex gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-600">
                            {{ $index + 1 }}
                        </span>
                        <p class="pt-0.5 text-sm leading-relaxed text-zinc-600">{{ $step }}</p>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="mt-8 rounded-2xl bg-zinc-50 p-5 text-sm leading-relaxed text-zinc-600">
            <p class="font-medium text-zinc-900">Catatan alamat server</p>
            <p class="mt-1">
                Aplikasi ini membungkus versi web {{ config('app.name') }}, jadi alamat servernya harus bisa diakses
                dari HP kamu. Kalau aplikasi menampilkan layar "tidak bisa terhubung", isi alamat server JasaKita
                (misalnya <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[12px]">http://192.168.1.10:8000</code>)
                di layar tersebut lalu tekan Coba lagi.
            </p>
        </div>
    </main>

    <footer class="border-t border-zinc-200">
        <div class="mx-auto flex max-w-3xl flex-col gap-2 px-4 py-8 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. Marketplace jasa pilihan.</p>
            <p>Pembayaran dilakukan tunai langsung ke mitra.</p>
        </div>
    </footer>
</body>
</html>
