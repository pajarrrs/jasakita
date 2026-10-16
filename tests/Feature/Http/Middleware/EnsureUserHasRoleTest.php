<?php

namespace Tests\Feature\Http\Middleware;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnsureUserHasRoleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleAreas')]
    public function test_allows_user_into_the_area_of_their_role(Role $role, string $routeName): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertOk();
    }

    /**
     * @return array<string, array{0: Role, 1: string}>
     */
    public static function roleAreas(): array
    {
        return [
            'dashboard pelanggan' => [Role::Customer, 'customer.dashboard'],
            'kategori pelanggan' => [Role::Customer, 'customer.categories.index'],
            'request pelanggan' => [Role::Customer, 'customer.requests.index'],
            'profil pelanggan' => [Role::Customer, 'customer.profile'],
            'dashboard mitra' => [Role::Partner, 'partner.dashboard'],
            'profil mitra' => [Role::Partner, 'partner.profile'],
            'layanan mitra' => [Role::Partner, 'partner.services.index'],
            'request mitra' => [Role::Partner, 'partner.requests.index'],
            'job mitra' => [Role::Partner, 'partner.jobs.index'],
            'verifikasi mitra' => [Role::Partner, 'partner.verification'],
            'dashboard admin' => [Role::Admin, 'admin.dashboard'],
            'kategori admin' => [Role::Admin, 'admin.categories.index'],
            'jasa admin' => [Role::Admin, 'admin.services.index'],
            'request admin' => [Role::Admin, 'admin.requests.index'],
            'pengguna admin' => [Role::Admin, 'admin.users.index'],
            'verifikasi admin' => [Role::Admin, 'admin.verifications.index'],
        ];
    }

    #[DataProvider('forbiddenCombinations')]
    public function test_forbids_user_from_the_area_of_another_role(Role $role, string $routeName): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertForbidden();
    }

    /**
     * @return array<string, array{0: Role, 1: string}>
     */
    public static function forbiddenCombinations(): array
    {
        return [
            'pelanggan ke area mitra' => [Role::Customer, 'partner.dashboard'],
            'pelanggan ke profil mitra' => [Role::Customer, 'partner.profile'],
            'pelanggan ke layanan mitra' => [Role::Customer, 'partner.services.index'],
            'pelanggan ke request mitra' => [Role::Customer, 'partner.requests.index'],
            'pelanggan ke job mitra' => [Role::Customer, 'partner.jobs.index'],
            'pelanggan ke verifikasi mitra' => [Role::Customer, 'partner.verification'],
            'pelanggan ke area admin' => [Role::Customer, 'admin.dashboard'],
            'pelanggan ke kategori admin' => [Role::Customer, 'admin.categories.index'],
            'pelanggan ke jasa admin' => [Role::Customer, 'admin.services.index'],
            'pelanggan ke request admin' => [Role::Customer, 'admin.requests.index'],
            'pelanggan ke pengguna admin' => [Role::Customer, 'admin.users.index'],
            'pelanggan ke verifikasi admin' => [Role::Customer, 'admin.verifications.index'],
            'mitra ke area pelanggan' => [Role::Partner, 'customer.dashboard'],
            'mitra ke kategori pelanggan' => [Role::Partner, 'customer.categories.index'],
            'mitra ke request pelanggan' => [Role::Partner, 'customer.requests.index'],
            'mitra ke profil pelanggan' => [Role::Partner, 'customer.profile'],
            'mitra ke area admin' => [Role::Partner, 'admin.dashboard'],
            'mitra ke kategori admin' => [Role::Partner, 'admin.categories.index'],
            'mitra ke jasa admin' => [Role::Partner, 'admin.services.index'],
            'mitra ke request admin' => [Role::Partner, 'admin.requests.index'],
            'mitra ke pengguna admin' => [Role::Partner, 'admin.users.index'],
            'mitra ke verifikasi admin' => [Role::Partner, 'admin.verifications.index'],
            'admin ke area pelanggan' => [Role::Admin, 'customer.dashboard'],
            'admin ke kategori pelanggan' => [Role::Admin, 'customer.categories.index'],
            'admin ke request pelanggan' => [Role::Admin, 'customer.requests.index'],
            'admin ke profil pelanggan' => [Role::Admin, 'customer.profile'],
            'admin ke area mitra' => [Role::Admin, 'partner.dashboard'],
            'admin ke profil mitra' => [Role::Admin, 'partner.profile'],
            'admin ke layanan mitra' => [Role::Admin, 'partner.services.index'],
            'admin ke request mitra' => [Role::Admin, 'partner.requests.index'],
            'admin ke job mitra' => [Role::Admin, 'partner.jobs.index'],
            'admin ke verifikasi mitra' => [Role::Admin, 'partner.verification'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_redirects_guest_to_login(string $routeName): void
    {
        $this->get(route($routeName))->assertRedirectToRoute('login');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedRoutes(): array
    {
        return [
            'area pelanggan' => ['customer.dashboard'],
            'kategori pelanggan' => ['customer.categories.index'],
            'request pelanggan' => ['customer.requests.index'],
            'profil pelanggan' => ['customer.profile'],
            'area mitra' => ['partner.dashboard'],
            'profil mitra' => ['partner.profile'],
            'layanan mitra' => ['partner.services.index'],
            'request mitra' => ['partner.requests.index'],
            'job mitra' => ['partner.jobs.index'],
            'verifikasi mitra' => ['partner.verification'],
            'area admin' => ['admin.dashboard'],
            'kategori admin' => ['admin.categories.index'],
            'jasa admin' => ['admin.services.index'],
            'request admin' => ['admin.requests.index'],
            'pengguna admin' => ['admin.users.index'],
            'verifikasi admin' => ['admin.verifications.index'],
        ];
    }
}
