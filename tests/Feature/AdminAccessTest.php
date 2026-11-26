<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac10_ensure_user_is_admin_middleware_protects_admin_routes(): void
    {
        // 1. Guest accessing GET /admin is redirected to /login
        $this->get('/admin')
            ->assertRedirect(route('login'));

        // 2. Customer accessing GET /admin receives 403 Forbidden
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();

        // 3. Admin accessing GET /admin is allowed and redirected to /admin/orders (200 OK)
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertRedirect(route('admin.orders.index'));

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSeeText('MANAJEMEN');
    }
}
