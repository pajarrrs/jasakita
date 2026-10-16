<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ config('app.name') }} — Marketplace Jasa Pilihan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-white font-sans text-zinc-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-zinc-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <div class="flex items-center gap-2">
                <span class="flex size-8 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">
                    JK
                </span>
                <span class="text-sm font-semibold">{{ config('app.name') }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('login') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900"
                >
                    Masuk
                </a>

                <a
                    href="{{ route('register') }}"
                    class="rounded-lg bg-teal-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-teal-700"
                >
                    Daftar
                </a>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute -top-32 right-0 size-96 rounded-full bg-teal-100/60 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 size-96 rounded-full bg-emerald-100/50 blur-3xl"></div>

            <div class="relative mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:py-24">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700">
                        <span class="size-1.5 rounded-full bg-teal-500"></span>
                        Jasa langka, tanpa ribet cari
                    </span>

                    <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight text-zinc-900 sm:text-5xl">
                        Butuh jasa yang
                        <span class="text-teal-600">jarang dipakai?</span>
                        Tinggal request.
                    </h1>

                    <p class="mt-5 max-w-xl text-base leading-relaxed text-zinc-600">
                        Servis AC, permak jaket, foto produk, potong rumput, hingga instalasi CCTV.
                        Pilih jasanya, kirim request, lalu biarkan mitra terbaik yang menawar untuk mengerjakannya.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                        >
                            Mulai cari jasa
                            <svg viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                            </svg>
                        </a>

                        <a
                            href="{{ route('register', ['role' => 'partner']) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-700 transition hover:border-teal-300 hover:text-teal-700"
                        >
                            Saya penyedia jasa
                        </a>
                    </div>

                    <dl class="mt-10 grid grid-cols-3 gap-4 border-t border-zinc-200 pt-6">
                        <div>
                            <dt class="text-xs text-zinc-500">Jasa aktif</dt>
                            <dd class="mt-1 text-2xl font-bold text-zinc-900">{{ $serviceCount }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs text-zinc-500">Kategori</dt>
                            <dd class="mt-1 text-2xl font-bold text-zinc-900">{{ $categories->count() }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs text-zinc-500">Pembayaran</dt>
                            <dd class="mt-1 text-2xl font-bold text-zinc-900">Tunai</dd>
                        </div>
                    </dl>
                </div>

                {{-- Ilustrasi kartu request --}}
                <div class="relative mx-auto w-full max-w-md">
                    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xl shadow-teal-900/5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="flex size-8 items-center justify-center rounded-lg bg-teal-50 text-xs font-semibold text-teal-700">
                                    JK
                                </span>
                                <div>
                                    <p class="text-sm font-semibold">Servis AC kamar tidur</p>
                                    <p class="text-xs text-zinc-500">Bandung &middot; 2 jam lalu</p>
                                </div>
                            </div>

                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                Menunggu
                            </span>
                        </div>

                        <div class="mt-4 space-y-2.5">
                            <div class="flex items-center justify-between rounded-xl border border-teal-200 bg-teal-50/60 p-3">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900">Mitra Contoh</p>
                                    <p class="text-xs text-zinc-500">Termasuk cuci &amp; isi freon</p>
                                </div>
                                <p class="text-sm font-semibold text-teal-700">Rp 165.000</p>
                            </div>

                            <div class="flex items-center justify-between rounded-xl border border-zinc-200 p-3">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900">Dewi Bersih</p>
                                    <p class="text-xs text-zinc-500">Fokus cuci dan vakum</p>
                                </div>
                                <p class="text-sm font-semibold text-zinc-900">Rp 150.000</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2 rounded-xl bg-zinc-50 p-3 text-xs text-zinc-500">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-teal-600">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                            </svg>
                            Pelanggan memilih penawaran terbaik, lalu bayar tunai setelah selesai.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Cara kerja --}}
        <section class="border-y border-zinc-200 bg-zinc-50">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Cara kerjanya simpel</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600">
                        Tidak perlu telepon sana-sini. Semua proses dari request sampai ulasan ada di satu tempat.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Pilih jasa', 'Telusuri kategori dan temukan jasa yang kamu butuhkan beserta patokan harganya.'],
                        ['Kirim request', 'Minta penawaran dari beberapa mitra sekaligus, atau pesan langsung ke mitra tercepat.'],
                        ['Mitra mengerjakan', 'Pantau status pekerjaan: dijadwalkan, dikerjakan, sampai selesai.'],
                        ['Bayar & beri ulasan', 'Bayar tunai setelah pekerjaan selesai, lalu beri rating untuk mitranya.'],
                    ] as $index => $step)
                        <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-teal-600 text-sm font-bold text-white">
                                {{ $index + 1 }}
                            </span>
                            <h3 class="mt-4 text-sm font-semibold text-zinc-900">{{ $step[0] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ $step[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Kategori --}}
        @if ($categories->isNotEmpty())
            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Kategori populer</h2>
                        <p class="mt-3 text-sm text-zinc-600">Dari perawatan rumah sampai kebutuhan kreatif.</p>
                    </div>

                    <a href="{{ route('register') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">
                        Lihat semua jasa &rarr;
                    </a>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('register') }}"
                            class="group flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:border-teal-300 hover:shadow-sm"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-900 group-hover:text-teal-700">
                                    {{ $category->name }}
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $category->services_count }} jasa tersedia</p>
                            </div>

                            <svg viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0 text-zinc-300 transition group-hover:text-teal-600">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Aplikasi Android --}}
        <section class="border-y border-zinc-200 bg-gradient-to-b from-zinc-50 to-white">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-3.5">
                            <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.2439 13.8533 7.8508 12 7.8508s-3.5902.3931-5.1367 1.0989L4.841 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3435-4.1021-2.6892-7.5743-6.1185-9.4396" />
                        </svg>
                        Aplikasi Android
                    </span>

                    <h2 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl">Bawa JasaKita di saku kamu</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600">
                        Dua aplikasi Android siap diunduh: satu untuk pelanggan, satu untuk mitra.
                        Pasang langsung dari sini, tanpa lewat Play Store.
                    </p>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach ($apps as $slug => $app)
                        <a
                            href="{{ route('downloads.show', $slug) }}"
                            class="group flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-lg hover:shadow-teal-900/5"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <span @class([
                                        'flex size-11 items-center justify-center rounded-2xl text-sm font-bold text-white',
                                        'bg-emerald-600' => $slug === 'mitra',
                                        'bg-teal-600' => $slug !== 'mitra',
                                    ])>
                                        {{ $slug === 'mitra' ? 'M' : 'JK' }}
                                    </span>

                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900 transition group-hover:text-teal-700">
                                            {{ $app['name'] }}
                                        </p>
                                        <p class="text-xs text-zinc-500">{{ $app['audience'] }} &middot; Android 8+</p>
                                    </div>
                                </div>

                                <span class="rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-[11px] font-medium text-teal-700">
                                    Gratis
                                </span>
                            </div>

                            <p class="text-sm leading-relaxed text-zinc-600">{{ $app['tagline'] }}</p>

                            <span class="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-teal-700">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                    <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" />
                                    <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" />
                                </svg>
                                Unduh APK
                                <svg viewBox="0 0 20 20" fill="currentColor" class="size-4 transition group-hover:translate-x-0.5">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA mitra --}}
        <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:pb-24">
            <div class="relative overflow-hidden rounded-3xl bg-teal-700 px-6 py-12 text-white sm:px-10 lg:py-16">
                <div class="pointer-events-none absolute -right-16 -top-24 size-72 rounded-full bg-teal-500/40 blur-3xl"></div>

                <div class="relative grid gap-8 lg:grid-cols-[1.4fr_1fr] lg:items-center">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                            Punya keahlian yang jarang dicari orang?
                        </h2>
                        <p class="mt-3 max-w-xl text-sm leading-relaxed text-teal-50">
                            Buka layananmu di JasaKita. Atur harga sendiri, pilih jasa yang kamu kuasai,
                            dan ambil job dari pelanggan di sekitarmu.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                        <a
                            href="{{ route('register', ['role' => 'partner']) }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-teal-800 transition hover:bg-teal-50"
                        >
                            Daftar sebagai mitra
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-teal-500 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-teal-600"
                        >
                            Masuk
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-zinc-200">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-8 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. Marketplace jasa pilihan.</p>
            <p>Pembayaran dilakukan tunai langsung ke mitra.</p>
        </div>
    </footer>
</body>
</html>
