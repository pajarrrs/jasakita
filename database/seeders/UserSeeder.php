<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo untuk pengembangan.
     *
     * @var array<int, array<string, string>>
     */
    private const USERS = [
        ['name' => 'Admin JasaKita', 'email' => 'admin@jasakita.test', 'phone' => '081100000001', 'role' => 'admin'],
        ['name' => 'Mitra Contoh', 'email' => 'mitra@jasakita.test', 'phone' => '081100000002', 'role' => 'partner'],
        ['name' => 'Pelanggan Contoh', 'email' => 'pelanggan@jasakita.test', 'phone' => '081100000003', 'role' => 'customer'],
        ['name' => 'Dewi Bersih', 'email' => 'mitra2@jasakita.test', 'phone' => '081100000004', 'role' => 'partner'],
        ['name' => 'Agus Listrik', 'email' => 'mitra3@jasakita.test', 'phone' => '081100000005', 'role' => 'partner'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::USERS as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'role' => Role::from($user['role']),
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
