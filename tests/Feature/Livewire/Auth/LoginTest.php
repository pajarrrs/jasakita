<?php

namespace Tests\Feature\Livewire\Auth;

use App\Enums\Role;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_for_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk');
    }

    public function test_redirects_authenticated_user_away_from_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirectToRoute('home');
    }

    public function test_authenticates_user_and_redirects_to_their_dashboard(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirectToRoute('customer.dashboard');

        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('roleDashboards')]
    public function test_redirects_each_role_to_its_own_dashboard(Role $role, string $routeName): void
    {
        $user = User::factory()->create(['role' => $role]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
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

    public function test_rejects_wrong_password_without_authenticating(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password-salah')
            ->call('login')
            ->assertHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_requires_email_and_password(): void
    {
        Livewire::test(Login::class)
            ->call('login')
            ->assertHasErrors([
                'email' => 'required',
                'password' => 'required',
            ]);
    }

    public function test_throttles_login_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Login::class)
                ->set('email', $user->email)
                ->set('password', 'password-salah')
                ->call('login');
        }

        $throttleMessagePrefix = Str::before(__('auth.throttle'), ':seconds');

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors([
                'email' => fn (array $rules, array $messages): bool => str_starts_with($messages[0] ?? '', $throttleMessagePrefix),
            ]);

        $this->assertGuest();
    }

    public function test_queues_remember_cookie_when_remember_is_requested(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('remember', true)
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);

        $recallerName = Auth::guard()->getRecallerName();

        $this->assertNotEmpty(
            collect(cookie()->getQueuedCookies())->filter(
                fn ($cookie) => $cookie->getName() === $recallerName,
            ),
        );
    }
}
