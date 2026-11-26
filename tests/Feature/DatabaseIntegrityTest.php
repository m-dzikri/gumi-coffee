<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac3_schema_excludes_out_of_scope_columns_and_enforces_relations_and_foreign_key_constraints(): void
    {
        $this->assertFalse(Schema::hasColumn('orders', 'order_type'));
        $this->assertFalse(Schema::hasColumn('orders', 'table_id'));
        $this->assertFalse(Schema::hasColumns('orders', ['order_type', 'table_id']));

        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($customer->isAdmin());
        $this->assertTrue($admin->isAdmin());

        $menu = Menu::create([
            'name' => 'Americano Braga',
            'slug' => 'americano-braga',
            'category' => 'Coffee',
            'description' => 'Double shot espresso arabika.',
            'price' => 20000,
            'image_path' => 'images/menus/americano-braga.svg',
            'is_available' => true,
        ]);

        $order = Order::create([
            'order_code' => 'GM-0001',
            'user_id' => $customer->id,
            'total' => 40000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'paid_at' => null,
            'notes' => 'Less ice',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
        ]);

        $this->assertCount(1, $customer->orders);
        $this->assertTrue($order->user->is($customer));
        $this->assertCount(1, $order->items);
        $this->assertTrue($orderItem->order->is($order));
        $this->assertTrue($orderItem->menu->is($menu));
        $this->assertCount(1, $menu->orderItems);

        // Deleting a menu sets order_items.menu_id to null while preserving snapshot fields
        $menu->delete();

        $orderItem->refresh();
        $this->assertNull($orderItem->menu_id);
        $this->assertSame('Americano Braga', $orderItem->menu_name);
        $this->assertSame(2, $orderItem->quantity);
        $this->assertSame(20000, $orderItem->unit_price);
        $this->assertSame(40000, $orderItem->subtotal);

        // Deleting a user who already owns an order is blocked by restrictOnDelete()
        $this->expectException(QueryException::class);
        $customer->delete();
    }
}
