<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Kategori jasa yang jarang dipakai sehari-hari.
     *
     * @var array<int, array<string, string>>
     */
    private const CATEGORIES = [
        [
            'name' => 'Servis Elektronik & Pendingin',
            'slug' => 'servis-elektronik-pendingin',
            'icon' => 'wrench-screwdriver',
            'description' => 'Perbaikan AC, kulkas, mesin cuci, dan perangkat elektronik rumah tangga lainnya.',
        ],
        [
            'name' => 'Jahit & Permak',
            'slug' => 'jahit-permak',
            'icon' => 'scissors',
            'description' => 'Jasa menjahit, permak pakaian, dan penyesuaian ukuran.',
        ],
        [
            'name' => 'Fotografi & Videografi',
            'slug' => 'fotografi-videografi',
            'icon' => 'camera',
            'description' => 'Dokumentasi acara, foto produk, dan kebutuhan visual lainnya.',
        ],
        [
            'name' => 'Urus Dokumen & Administrasi',
            'slug' => 'urus-dokumen-administrasi',
            'icon' => 'document-text',
            'description' => 'Bantuan pengurusan surat, perizinan, dan keperluan administrasi.',
        ],
        [
            'name' => 'Kebersihan Khusus',
            'slug' => 'kebersihan-khusus',
            'icon' => 'sparkles',
            'description' => 'Bersih-bersih pasca renovasi, cuci sofa, karpet, dan perawatan khusus.',
        ],
        [
            'name' => 'Perawatan Mesin & Kendaraan',
            'slug' => 'perawatan-mesin-kendaraan',
            'icon' => 'cog-6-tooth',
            'description' => 'Servis genset, mesin industri, dan perawatan kendaraan panggilan.',
        ],
        [
            'name' => 'Perawatan Rumah & Taman',
            'slug' => 'perawatan-rumah-taman',
            'icon' => 'home',
            'description' => 'Potong rumput, perawatan tanaman, dan perbaikan rumah skala kecil.',
        ],
        [
            'name' => 'Jasa Teknis Khusus',
            'slug' => 'jasa-teknis-khusus',
            'icon' => 'key',
            'description' => 'Instalasi CCTV, jasa buka kunci, dan pekerjaan teknis lainnya.',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $sortOrder => $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ],
            );
        }
    }
}
