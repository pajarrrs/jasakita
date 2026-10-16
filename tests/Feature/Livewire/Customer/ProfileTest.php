<?php

namespace Tests\Feature\Livewire\Customer;

use App\Livewire\Customer\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_current_customer_data(): void
    {
        $customer = User::factory()->create([
            'name' => 'Rina Pelanggan',
            'phone' => '081234567890',
        ]);

        Livewire::actingAs($customer)
            ->test(Profile::class)
            ->assertSet('name', 'Rina Pelanggan')
            ->assertSet('phone', '081234567890');
    }

    public function test_updates_the_name_and_phone(): void
    {
        $customer = User::factory()->create();

        Livewire::actingAs($customer)
            ->test(Profile::class)
            ->set('name', 'Rina Baru')
            ->set('phone', '081200000009')
            ->call('save')
            ->assertHasNoErrors();

        $customer->refresh();

        $this->assertSame('Rina Baru', $customer->name);
        $this->assertSame('081200000009', $customer->phone);
    }

    public function test_requires_a_name_and_a_valid_phone(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Profile::class)
            ->set('name', '')
            ->set('phone', '123')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'phone' => 'min']);
    }

    public function test_updates_the_password_with_the_current_password(): void
    {
        $customer = User::factory()->create(['password' => 'rahasia123']);

        Livewire::actingAs($customer)
            ->test(Profile::class)
            ->set('currentPassword', 'rahasia123')
            ->set('password', 'passwordbaru9')
            ->set('password_confirmation', 'passwordbaru9')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('passwordbaru9', $customer->refresh()->password));
    }

    public function test_rejects_the_wrong_current_password(): void
    {
        $customer = User::factory()->create(['password' => 'rahasia123']);

        Livewire::actingAs($customer)
            ->test(Profile::class)
            ->set('currentPassword', 'salahsekali')
            ->set('password', 'passwordbaru9')
            ->set('password_confirmation', 'passwordbaru9')
            ->call('updatePassword')
            ->assertHasErrors(['currentPassword' => 'current_password']);
    }

    public function test_rejects_a_new_password_shorter_than_eight_characters(): void
    {
        $customer = User::factory()->create(['password' => 'rahasia123']);

        Livewire::actingAs($customer)
            ->test(Profile::class)
            ->set('currentPassword', 'rahasia123')
            ->set('password', 'pendek')
            ->set('password_confirmation', 'pendek')
            ->call('updatePassword')
            ->assertHasErrors([
                'password' => __('validation.min.string', ['attribute' => 'password', 'min' => 8]),
            ]);
    }
}
