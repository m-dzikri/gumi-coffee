<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_are_forced_to_customer_role_even_if_admin_role_is_injected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('menus.index', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertFalse($user->isAdmin());
    }
}
