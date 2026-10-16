<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_landing_page_to_guests(): void
    {
        ServiceCategory::factory()->create(['name' => 'Servis Genset']);
        Service::factory()->create(['name' => 'Servis Genset Rumah']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Butuh jasa yang')
            ->assertSee('Servis Genset')
            ->assertSee('Daftar sebagai mitra')
            ->assertSee(route('login'), false);
    }

    #[DataProvider('roleDashboards')]
    public function test_redirects_authenticated_user_to_their_dashboard(Role $role, string $routeName): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirectToRoute($routeName);
    }

    /**
     * @return array<string, array{0: Role, 1: string}>
     */
    public static function roleDashboards(): array
    {
        return [
            'pelanggan' => [Role::Customer, 'customer.dashboard'],
            'mitra' => [Role::Partner, 'partner.dashboard'],
            'admin' => [Role::Admin, 'admin.dashboard'],
        ];
    }
}
