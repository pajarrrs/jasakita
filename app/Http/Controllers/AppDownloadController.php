<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class AppDownloadController extends Controller
{
    /**
     * Halaman detail unduhan aplikasi Android.
     */
    public function show(string $app): View
    {
        $application = $this->application($app);

        return view('downloads.app', [
            'app' => $application,
            'downloadUrl' => Storage::disk('public')->url($application['file']),
            'downloadAvailable' => Storage::disk('public')->exists($application['file']),
            'fileSize' => $this->fileSize($application['file']),
        ]);
    }

    /**
     * Ambil metadata aplikasi dari konfigurasi.
     *
     * @return array<string, mixed>
     */
    private function application(string $app): array
    {
        $applications = config('downloads.apps', []);

        abort_unless(array_key_exists($app, $applications), 404);

        return ['slug' => $app, ...$applications[$app]];
    }

    /**
     * Ukuran berkas APK dalam satuan yang mudah dibaca.
     */
    private function fileSize(string $file): ?string
    {
        if (! Storage::disk('public')->exists($file)) {
            return null;
        }

        $bytes = Storage::disk('public')->size($file);

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    }
}
