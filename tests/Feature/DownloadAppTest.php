<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_offers_both_android_apps_as_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bawa JasaKita di saku kamu')
            ->assertSee('Unduh APK')
            ->assertSee(route('downloads.show', 'pelanggan'))
            ->assertSee(route('downloads.show', 'mitra'));
    }

    public function test_the_customer_app_download_page_renders_with_the_apk_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('apk/app-jasa.apk', 'dummy');

        $this->get(route('downloads.show', 'pelanggan'))
            ->assertOk()
            ->assertSee('App Pelanggan')
            ->assertSee('JasaKita-Pelanggan-1.0.apk')
            ->assertSee('/storage/apk/app-jasa.apk')
            ->assertSee('1 KB');
    }

    public function test_the_partner_app_download_page_renders_with_the_apk_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('apk/app-mitra.apk', 'dummy');

        $this->get(route('downloads.show', 'mitra'))
            ->assertOk()
            ->assertSee('App Mitra')
            ->assertSee('JasaKita-Mitra-1.0.apk')
            ->assertSee('/storage/apk/app-mitra.apk');
    }

    public function test_the_download_page_tells_visitors_when_the_apk_is_not_built_yet(): void
    {
        Storage::fake('public');

        $this->get(route('downloads.show', 'pelanggan'))
            ->assertOk()
            ->assertSee('Berkas APK belum tersedia');
    }

    public function test_an_unknown_app_slug_is_not_found(): void
    {
        $this->get('/unduh/aplikasi-asing')->assertNotFound();
    }
}
