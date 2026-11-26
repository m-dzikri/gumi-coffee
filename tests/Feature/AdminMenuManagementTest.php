<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac6_admin_menu_crud_price_validation_and_automatic_unique_slug_generation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // Customer is forbidden from accessing /admin/menus*
        $this->actingAs($customer)->get(route('admin.menus.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.menus.create'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.menus.store'), [
            'name' => 'Kopi Susu Gumi',
            'category' => 'Coffee',
            'price' => 25000,
        ])->assertForbidden();

        // Admin can view index and create pages with single h1
        $indexResponse = $this->actingAs($admin)->get(route('admin.menus.index'));
        $indexResponse->assertOk();
        $this->assertSame(1, substr_count((string) $indexResponse->getContent(), '<h1'));

        $createResponse = $this->actingAs($admin)->get(route('admin.menus.create'));
        $createResponse->assertOk();
        $this->assertSame(1, substr_count((string) $createResponse->getContent(), '<h1'));

        // 1. Create two menus with the exact same name 'Kopi Susu Gumi' -> unique slugs 'kopi-susu-gumi' and 'kopi-susu-gumi-2'
        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'name' => 'Kopi Susu Gumi',
                'category' => 'Coffee',
                'description' => 'Kopi susu gula aren khas Gumi.',
                'price' => 25000,
                'image_path' => 'images/menus/gumi-signature.svg',
                'is_available' => true,
            ])
            ->assertRedirect(route('admin.menus.index'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'name' => 'Kopi Susu Gumi',
                'category' => 'Coffee',
                'description' => 'Kopi susu edisi kedua.',
                'price' => 27000,
            ])
            ->assertRedirect(route('admin.menus.index'))
            ->assertSessionHasNoErrors();

        $menus = Menu::query()->orderBy('id')->get();
        $this->assertCount(2, $menus);
        $this->assertSame('kopi-susu-gumi', $menus[0]->slug);
        $this->assertSame('kopi-susu-gumi-2', $menus[1]->slug);

        // 2. Edit and update menu via PUT /admin/menus/{menu}
        $editResponse = $this->actingAs($admin)->get(route('admin.menus.edit', $menus[0]));
        $editResponse->assertOk();
        $this->assertSame(1, substr_count((string) $editResponse->getContent(), '<h1'));

        $this->actingAs($admin)
            ->put(route('admin.menus.update', $menus[0]), [
                'name' => 'Kopi Susu Gumi Spesial',
                'category' => 'Coffee',
                'description' => 'Deskripsi diperbarui.',
                'price' => 29000,
                'image_path' => 'images/menus/gumi-signature.svg',
                'is_available' => false,
            ])
            ->assertRedirect(route('admin.menus.index'))
            ->assertSessionHasNoErrors();

        $menus[0]->refresh();
        $this->assertSame('Kopi Susu Gumi Spesial', $menus[0]->name);
        $this->assertSame('kopi-susu-gumi-spesial', $menus[0]->slug);
        $this->assertSame(29000, $menus[0]->price);
        $this->assertFalse($menus[0]->is_available);

        // 3. Reject price < 1000 and invalid category on both store and update
        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'name' => 'Menu Murah Invalid',
                'category' => 'Coffee',
                'price' => 500,
            ])
            ->assertSessionHasErrors('price');

        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'name' => 'Menu Kategori Invalid',
                'category' => 'Invalid',
                'price' => 20000,
            ])
            ->assertSessionHasErrors('category');

        $this->actingAs($admin)
            ->put(route('admin.menus.update', $menus[0]), [
                'name' => 'Kopi Susu Gumi Spesial',
                'category' => 'Invalid',
                'price' => 500,
            ])
            ->assertSessionHasErrors(['price', 'category']);
    }

    public function test_ac7_admin_can_quick_toggle_menu_availability_and_reflect_on_public_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $menu = Menu::query()->create([
            'name' => 'Matcha Cloud',
            'slug' => 'matcha-cloud',
            'category' => 'Non-Coffee',
            'description' => 'Matcha Uji creamy.',
            'price' => 28000,
            'image_path' => null,
            'is_available' => true,
        ]);

        // 1. Toggle true -> false
        $this->actingAs($admin)
            ->patch(route('admin.menus.toggle-availability', $menu))
            ->assertRedirect(route('admin.menus.index'))
            ->assertSessionHasNoErrors();

        $this->assertFalse($menu->fresh()->is_available);

        // Reflected on public catalog GET /menus
        $this->get(route('menus.index'))
            ->assertOk()
            ->assertSeeText('Matcha Cloud')
            ->assertSeeText('Habis / Tidak Tersedia');

        // 2. Toggle false -> true
        $this->actingAs($admin)
            ->patch(route('admin.menus.toggle-availability', $menu))
            ->assertRedirect(route('admin.menus.index'))
            ->assertSessionHasNoErrors();

        $this->assertTrue($menu->fresh()->is_available);
    }

    public function test_ac8_deleting_menu_preserves_order_item_snapshots_and_customer_order_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $menuX = Menu::query()->create([
            'name' => 'Americano Dingin',
            'slug' => 'americano-dingin',
            'category' => 'Coffee',
            'description' => 'Double shot espresso dingin.',
            'price' => 22000,
            'image_path' => null,
            'is_available' => true,
        ]);

        $order = Order::query()->create([
            'order_code' => 'GM-0088',
            'user_id' => $customer->id,
            'total' => 44000,
            'status' => Order::STATUS_COMPLETED,
            'payment_method' => Order::METHOD_CASH,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
            'notes' => null,
        ]);

        $orderItem = OrderItem::query()->create([
            'order_id' => $order->id,
            'menu_id' => $menuX->id,
            'menu_name' => 'Americano Dingin',
            'quantity' => 2,
            'unit_price' => 22000,
            'subtotal' => 44000,
        ]);

        // Admin deletes Menu X
        $this->actingAs($admin)
            ->delete(route('admin.menus.destroy', $menuX))
            ->assertRedirect(route('admin.menus.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('menus', ['id' => $menuX->id]);

        // OrderItem row remains intact with menu_id = null and snapshot values preserved
        $orderItem->refresh();
        $this->assertNull($orderItem->menu_id);
        $this->assertSame('Americano Dingin', $orderItem->menu_name);
        $this->assertSame(22000, $orderItem->unit_price);
        $this->assertSame(44000, $orderItem->subtotal);

        // Customer order detail page still renders 200 OK with snapshot item name
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSeeText('GM-0088')
            ->assertSeeText('Americano Dingin');
    }
}
