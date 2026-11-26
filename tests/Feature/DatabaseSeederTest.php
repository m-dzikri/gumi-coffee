<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac4_database_seeder_creates_demo_accounts_and_menus_with_existing_static_images(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@gumicoffee.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());

        $customer = User::where('email', 'customer@gumicoffee.test')->first();
        $this->assertNotNull($customer);
        $this->assertSame('customer', $customer->role);
        $this->assertFalse($customer->isAdmin());

        $menus = Menu::all();
        $this->assertGreaterThanOrEqual(6, $menus->count());

        $categories = $menus->pluck('category')->unique()->all();
        $this->assertContains('Coffee', $categories);
        $this->assertContains('Non-Coffee', $categories);
        $this->assertContains('Pastry', $categories);

        $this->assertGreaterThanOrEqual(5, $menus->where('is_available', true)->count());
        $this->assertGreaterThanOrEqual(1, $menus->where('is_available', false)->count());

        foreach ($menus as $menu) {
            $this->assertNotNull($menu->image_path);
            $this->assertFileExists(public_path($menu->image_path));
        }
    }
}
