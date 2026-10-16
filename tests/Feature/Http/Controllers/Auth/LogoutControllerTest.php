<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_out_authenticated_user_and_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirectToRoute('login');

        $this->assertGuest();
    }

    public function test_redirects_guest_to_login(): void
    {
        $this->post(route('logout'))->assertRedirectToRoute('login');
    }
}
