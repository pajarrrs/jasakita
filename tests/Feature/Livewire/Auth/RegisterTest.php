<?php

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'role' => 'customer',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ];
    }

    public function test_register_page_renders_for_guests(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar');
    }

    public function test_redirects_authenticated_user_away_from_register_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirectToRoute('home');
    }

    public function test_registers_customer_with_hashed_password_and_redirects_to_customer_dashboard(): void
    {
        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirectToRoute('customer.dashboard');

        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('081234567890', $user->phone);
        $this->assertTrue($user->isCustomer());
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_registers_partner_and_redirects_to_partner_dashboard(): void
    {
        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->set('role', 'partner')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirectToRoute('partner.dashboard');

        $this->assertTrue(User::where('email', 'budi@example.com')->firstOrFail()->isPartner());
    }

    public function test_rejects_admin_role_registration(): void
    {
        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->set('role', 'admin')
            ->call('register')
            ->assertHasErrors(['role' => 'in']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->call('register')
            ->assertHasErrors(['email' => 'unique']);

        $this->assertGuest();
    }

    public function test_requires_name_email_phone_and_password(): void
    {
        Livewire::test(Register::class)
            ->call('register')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'password' => 'required',
            ]);
    }

    public function test_rejects_password_shorter_than_eight_characters(): void
    {
        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->set('password', 'rahasia')
            ->set('password_confirmation', 'rahasia')
            ->call('register')
            ->assertHasErrors([
                'password' => __('validation.min.string', ['attribute' => 'password', 'min' => 8]),
            ]);
    }

    public function test_rejects_unconfirmed_password(): void
    {
        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->set('password_confirmation', 'beda-sekali')
            ->call('register')
            ->assertHasErrors(['password' => 'confirmed']);
    }

    public function test_rejects_phone_shorter_than_nine_characters(): void
    {
        Livewire::test(Register::class)
            ->set($this->validPayload())
            ->set('phone', '08123')
            ->call('register')
            ->assertHasErrors(['phone' => 'min']);
    }

    public function test_prefills_the_partner_role_from_the_query_string(): void
    {
        Livewire::withQueryParams(['role' => 'partner'])
            ->test(Register::class)
            ->assertSet('role', 'partner');
    }

    public function test_ignores_an_invalid_role_query_string(): void
    {
        Livewire::withQueryParams(['role' => 'admin'])
            ->test(Register::class)
            ->assertSet('role', 'customer');
    }
}
