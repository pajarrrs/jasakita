<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Memastikan semua halaman utama tetap bisa dirender dengan data demo hasil seeder.
 */
class SeededPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('authenticatedPages')]
    public function test_seeded_pages_render_for_each_role(string $email, string $routeName): void
    {
        $this->seed();

        $user = User::where('email', $email)->firstOrFail();

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertOk();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function authenticatedPages(): array
    {
        return [
            'dashboard pelanggan' => ['pelanggan@jasakita.test', 'customer.dashboard'],
            'kategori pelanggan' => ['pelanggan@jasakita.test', 'customer.categories.index'],
            'request pelanggan' => ['pelanggan@jasakita.test', 'customer.requests.index'],
            'profil pelanggan' => ['pelanggan@jasakita.test', 'customer.profile'],
            'dashboard mitra' => ['mitra@jasakita.test', 'partner.dashboard'],
            'request mitra' => ['mitra@jasakita.test', 'partner.requests.index'],
            'job mitra' => ['mitra@jasakita.test', 'partner.jobs.index'],
            'profil mitra' => ['mitra@jasakita.test', 'partner.profile'],
            'verifikasi mitra' => ['mitra@jasakita.test', 'partner.verification'],
            'dashboard admin' => ['admin@jasakita.test', 'admin.dashboard'],
            'kategori admin' => ['admin@jasakita.test', 'admin.categories.index'],
            'jasa admin' => ['admin@jasakita.test', 'admin.services.index'],
            'request admin' => ['admin@jasakita.test', 'admin.requests.index'],
            'pengguna admin' => ['admin@jasakita.test', 'admin.users.index'],
            'verifikasi admin' => ['admin@jasakita.test', 'admin.verifications.index'],
        ];
    }

    public function test_landing_page_renders_with_seeded_catalog(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Daftar sebagai mitra');
    }

    public function test_seeded_catalog_detail_pages_render(): void
    {
        $this->seed();

        $customer = User::where('email', 'pelanggan@jasakita.test')->firstOrFail();
        $category = ServiceCategory::query()->where('is_active', true)->firstOrFail();
        $service = Service::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)
            ->get(route('customer.categories.show', $category))
            ->assertOk();

        $this->actingAs($customer)
            ->get(route('customer.services.show', $service))
            ->assertOk();

        $this->actingAs($customer)
            ->get(route('customer.requests.create', ['service' => $service, 'mode' => 'quote']))
            ->assertOk();
    }

    public function test_seeded_request_and_job_detail_pages_render(): void
    {
        $this->seed();

        $customer = User::where('email', 'pelanggan@jasakita.test')->firstOrFail();
        $partner = User::where('email', 'mitra@jasakita.test')->firstOrFail();

        foreach ($customer->serviceRequests as $serviceRequest) {
            $this->actingAs($customer)
                ->get(route('customer.requests.show', $serviceRequest))
                ->assertOk();
        }

        foreach ($partner->assignedJobs as $job) {
            $this->actingAs($partner)
                ->get(route('partner.jobs.show', $job))
                ->assertOk();
        }

        $this->assertNotEmpty($customer->serviceRequests);
        $this->assertNotEmpty($partner->assignedJobs);
    }
}
