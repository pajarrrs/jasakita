<?php

namespace Database\Seeders;

use App\Enums\PriceUnit;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    /**
     * Layanan contoh per slug kategori.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private const SERVICES = [
        'servis-elektronik-pendingin' => [
            ['name' => 'Servis AC', 'base_price' => 150000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Pembersihan dan perbaikan ringan AC. Harga akhir menyesuaikan kondisi unit.'],
            ['name' => 'Servis Kulkas', 'base_price' => 200000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-3 jam', 'description' => 'Pengecekan dan perbaikan kulkas yang tidak dingin atau bocor.'],
            ['name' => 'Servis Mesin Cuci', 'base_price' => 175000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Perbaikan mesin cuci yang tidak berputar, bocor, atau berisik.'],
            ['name' => 'Servis TV & Perangkat Audio', 'base_price' => 150000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Pengecekan dan perbaikan TV, soundbar, serta perangkat audio rumah.'],
        ],
        'jahit-permak' => [
            ['name' => 'Permak Pakaian', 'base_price' => 50000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 hari', 'description' => 'Mengecilkan, memendekkan, atau memperbaiki pakaian yang tidak muat lagi.'],
            ['name' => 'Jahit Pakaian Custom', 'base_price' => 250000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '3-7 hari', 'description' => 'Jahit pakaian sesuai ukuran dan desain yang kamu inginkan.'],
            ['name' => 'Permak Gaun & Kebaya', 'base_price' => 350000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '3-5 hari', 'description' => 'Permak atau penyesuaian gaun, kebaya, dan pakaian acara.'],
        ],
        'fotografi-videografi' => [
            ['name' => 'Dokumentasi Acara', 'base_price' => 1500000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '4-8 jam', 'description' => 'Dokumentasi foto dan video untuk acara keluarga, kantor, atau pernikahan.'],
            ['name' => 'Foto Produk', 'base_price' => 750000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '2-4 jam', 'description' => 'Foto produk dengan penataan dan pencahayaan untuk kebutuhan jualan.'],
            ['name' => 'Video Profil Usaha', 'base_price' => 2500000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 hari', 'description' => 'Pembuatan video profil singkat untuk keperluan promosi usaha.'],
        ],
        'urus-dokumen-administrasi' => [
            ['name' => 'Pendampingan Urus Surat', 'base_price' => 200000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-3 hari', 'description' => 'Pendampingan pengurusan surat keterangan dan dokumen kependudukan.'],
            ['name' => 'Urus Perpanjangan Dokumen', 'base_price' => 250000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-3 hari', 'description' => 'Bantuan proses perpanjangan dokumen dan izin yang masih berlaku.'],
        ],
        'kebersihan-khusus' => [
            ['name' => 'Bersih Pasca Renovasi', 'base_price' => 500000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '3-6 jam', 'description' => 'Membersihkan sisa debu, cat, dan material setelah renovasi.'],
            ['name' => 'Cuci Sofa & Karpet', 'base_price' => 75000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Cuci sofa, kasur, atau karpet dengan alat basah dan pengering.'],
            ['name' => 'Fogging & Disinfeksi', 'base_price' => 300000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Pengasapan dan disinfeksi ruangan untuk membasmi hama serta kuman.'],
        ],
        'perawatan-mesin-kendaraan' => [
            ['name' => 'Servis Genset', 'base_price' => 500000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '2-4 jam', 'description' => 'Perawatan dan perbaikan genset rumah maupun usaha.'],
            ['name' => 'Servis Ringan Kendaraan Panggilan', 'base_price' => 250000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-3 jam', 'description' => 'Servis ringan kendaraan langsung di lokasi kamu.'],
        ],
        'perawatan-rumah-taman' => [
            ['name' => 'Perawatan Taman', 'base_price' => 75000, 'price_unit' => PriceUnit::PerHour, 'duration_estimate' => '2-4 jam', 'description' => 'Merapikan dan merawat taman, termasuk pemangkasan dan pemupukan.'],
            ['name' => 'Potong Rumput', 'base_price' => 150000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Potong rumput dan bersihkan halaman dengan alat pemotong.'],
            ['name' => 'Perbaikan Rumah Ringan', 'base_price' => 300000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '2-5 jam', 'description' => 'Perbaikan atap bocor, dinding, pintu, dan kerusakan kecil lainnya.'],
        ],
        'jasa-teknis-khusus' => [
            ['name' => 'Instalasi CCTV', 'base_price' => 400000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '2-4 jam', 'description' => 'Pemasangan dan pengaturan kamera CCTV di rumah atau tempat usaha.'],
            ['name' => 'Jasa Buka Kunci', 'base_price' => 250000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => 'kurang dari 1 jam', 'description' => 'Buka kunci pintu, lemari, atau kendaraan yang terkunci tanpa perusakan.'],
            ['name' => 'Instalasi & Servis Antena', 'base_price' => 200000, 'price_unit' => PriceUnit::PerJob, 'duration_estimate' => '1-2 jam', 'description' => 'Pemasangan dan perbaikan antena TV serta penerima sinyal.'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::SERVICES as $categorySlug => $services) {
            $category = ServiceCategory::where('slug', $categorySlug)->firstOrFail();

            foreach ($services as $service) {
                Service::updateOrCreate(
                    ['slug' => Str::slug($service['name'])],
                    [
                        ...$service,
                        'service_category_id' => $category->id,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
