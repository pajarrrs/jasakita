<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aplikasi Android JasaKita
    |--------------------------------------------------------------------------
    |
    | Metadata aplikasi Android yang ditawarkan di halaman depan dan halaman
    | unduhan. Berkas APK dibangun lewat resources/android/build.sh dan
    | disimpan di storage/app/public/apk.
    |
    */

    'apps' => [

        'pelanggan' => [
            'name' => 'JasaKita',
            'audience' => 'App Pelanggan',
            'tagline' => 'Cari jasa langka, kirim request, dan pantau pengerjaan langsung dari HP.',
            'file' => 'apk/app-jasa.apk',
            'filename' => 'JasaKita-Pelanggan-1.0.apk',
            'version' => '1.0',
            'highlights' => [
                'Telusuri kategori jasa dan kirim request hanya dalam beberapa langkah.',
                'Terima penawaran harga dari beberapa mitra, lalu pilih yang paling cocok.',
                'Chat mitra, pantau status pekerjaan, dan beri ulasan setelah selesai.',
            ],
        ],

        'mitra' => [
            'name' => 'JasaKita Mitra',
            'audience' => 'App Mitra',
            'tagline' => 'Terima job, kirim penawaran, dan kelola jadwal kerja dari HP.',
            'file' => 'apk/app-mitra.apk',
            'filename' => 'JasaKita-Mitra-1.0.apk',
            'version' => '1.0',
            'highlights' => [
                'Lihat request masuk sesuai layanan yang kamu kuasai.',
                'Ambil job langsung atau kirim penawaran dengan harga sendiri.',
                'Chat pelanggan, atur jadwal, dan lengkapi verifikasi identitas.',
            ],
        ],

    ],

];
