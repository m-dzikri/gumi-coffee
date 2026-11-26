<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createCustomer(): User
    {
        return User::factory()->create([
            'role' => 'customer',
        ]);
    }

    private function createMenu(array $overrides = []): Menu
    {
        return Menu::query()->create(array_merge([
            'name' => 'Gumi Signature Kopi Susu',
            'slug' => 'menu-'.uniqid(),
            'category' => 'Coffee',
            'description' => 'Kopi susu gula aren.',
            'price' => 20000,
            'image_path' => 'images/menus/kopi-susu-gumi.svg',
            'is_available' => true,
        ], $overrides));
    }

    public function test_ac3_checkout_page_displays_takeaway_info_and_validates_notes_max_500_chars(): void
    {
        Carbon::setTestNow(Carbon::createFromTimeString('10:00:00', 'Asia/Jakarta'));
        config(['gumi.estimated_pickup_text' => 'Estimasi siap 10-15 menit']);

        $customer = $this->createCustomer();
        $menu = $this->createMenu(['price' => 22000]);

        $this->actingAs($customer)->post('/cart/items', [
            'menu_id' => $menu->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($customer)->get('/checkout');
        $response->assertOk();
        $response->assertSeeText('Takeaway / Pick-up');
        $response->assertSeeText('Estimasi siap 10-15 menit');
        $response->assertSee('value="cash"', false);
        $response->assertSee('value="qris_dummy"', false);
        $this->assertSame(1, substr_count((string) $response->getContent(), '<h1'));

        // Notes with 501 characters must fail validation on 'notes'
        $tooLongNotes = str_repeat('A', 501);
        $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'cash',
            'notes' => $tooLongNotes,
        ])->assertSessionHasErrors('notes');
        $this->assertSame(0, Order::query()->count());

        // Notes with exactly 500 characters must pass validation
        $validNotes = str_repeat('B', 500);
        $successResponse = $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'cash',
            'notes' => $validNotes,
        ]);

        $successResponse->assertSessionHasNoErrors();
        $this->assertSame(1, Order::query()->count());
        $this->assertSame($validNotes, Order::query()->firstOrFail()->notes);
    }

    public function test_ac4_checkout_is_rejected_outside_operating_hours_in_asia_jakarta(): void
    {
        config([
            'gumi.operating_hours.open' => '08:00',
            'gumi.operating_hours.close' => '22:00',
        ]);

        $customer = $this->createCustomer();
        $menu = $this->createMenu(['price' => 24000]);

        $this->actingAs($customer)->post('/cart/items', [
            'menu_id' => $menu->id,
            'quantity' => 2,
        ]);

        // After closing time (22:01 WIB)
        Carbon::setTestNow(Carbon::createFromTimeString('22:01:00', 'Asia/Jakarta'));
        $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'cash',
            'notes' => 'Pesanan malam',
        ])->assertSessionHasErrors('checkout');

        $this->assertSame(0, Order::query()->count());

        // Before opening time (07:59 WIB)
        Carbon::setTestNow(Carbon::createFromTimeString('07:59:00', 'Asia/Jakarta'));
        $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'qris_dummy',
            'notes' => 'Pesanan pagi buta',
        ])->assertSessionHasErrors('checkout');

        $this->assertSame(0, Order::query()->count());
        $this->assertNotEmpty(session('cart'));
    }

    public function test_ac5_checkout_rechecks_menu_availability_and_rolls_back_atomically_when_menu_unavailable_or_deleted(): void
    {
        Carbon::setTestNow(Carbon::createFromTimeString('11:00:00', 'Asia/Jakarta'));

        $customer = $this->createCustomer();
        $menuA = $this->createMenu(['name' => 'Americano Klasik', 'price' => 18000, 'is_available' => true]);
        $menuB = $this->createMenu(['name' => 'Kopi Gula Aren', 'price' => 24000, 'is_available' => true]);

        $this->actingAs($customer)->post('/cart/items', [
            'menu_id' => $menuA->id,
            'quantity' => 1,
        ]);
        $this->actingAs($customer)->post('/cart/items', [
            'menu_id' => $menuB->id,
            'quantity' => 2,
        ]);

        // Scenario 1: Menu B becomes is_available = false right before POST /checkout
        $menuB->update(['is_available' => false]);

        // Verify GET /checkout summary card displays "Tidak Tersedia" badge for unavailable item
        $checkoutViewWithUnavailableItem = $this->actingAs($customer)->get('/checkout');
        $checkoutViewWithUnavailableItem->assertOk();
        $checkoutViewWithUnavailableItem->assertSeeText('Kopi Gula Aren');
        $checkoutViewWithUnavailableItem->assertSeeText('Tidak Tersedia');

        $response1 = $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'cash',
        ]);

        $response1->assertSessionHasErrors('checkout');
        $this->assertStringContainsString(
            'Kopi Gula Aren',
            (string) session('errors')->first('checkout')
        );
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, OrderItem::query()->count());
        $this->assertCount(2, session('cart', []));

        // Scenario 2: Menu B is deleted from database right before POST /checkout
        $menuB->delete();

        $response2 = $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'qris_dummy',
        ]);

        $response2->assertSessionHasErrors('checkout');
        $this->assertStringContainsString(
            'Kopi Gula Aren',
            (string) session('errors')->first('checkout')
        );
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, OrderItem::query()->count());
        $this->assertCount(2, session('cart', []));
    }

    public function test_ac6_checkout_calculates_price_purely_on_server_generates_order_code_saves_snapshots_and_clears_cart(): void
    {
        Carbon::setTestNow(Carbon::createFromTimeString('10:00:00', 'Asia/Jakarta'));

        $customer = $this->createCustomer();
        $menu1 = $this->createMenu([
            'name' => 'Kopi Susu Gumi',
            'price' => 20000,
            'is_available' => true,
        ]);
        $menu2 = $this->createMenu([
            'name' => 'Butter Croissant',
            'price' => 15000,
            'is_available' => true,
        ]);

        $this->actingAs($customer)->post('/cart/items', [
            'menu_id' => $menu1->id,
            'quantity' => 2,
        ]);
        $this->actingAs($customer)->post('/cart/items', [
            'menu_id' => $menu2->id,
            'quantity' => 1,
        ]);

        // Send POST /checkout with tampered client prices and status fields
        $response = $this->actingAs($customer)->post('/checkout', [
            'payment_method' => 'qris_dummy',
            'notes' => 'Es sedikit saja',
            'total' => 1000,
            'unit_price' => 500,
            'price' => 100,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_code' => 'GM-9999',
        ]);

        $this->assertSame(1, Order::query()->count());
        $order = Order::query()->with('items')->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));

        // Verify server-calculated total: (20000 * 2) + (15000 * 1) = 55000
        $this->assertSame(55000, $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('qris_dummy', $order->payment_method);
        $this->assertNull($order->paid_at);
        $this->assertSame('Es sedikit saja', $order->notes);

        $expectedOrderCode = 'GM-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
        $this->assertSame($expectedOrderCode, $order->order_code);

        // Verify order_items snapshots
        $this->assertCount(2, $order->items);

        $item1 = $order->items->firstWhere('menu_id', $menu1->id);
        $this->assertNotNull($item1);
        $this->assertSame('Kopi Susu Gumi', $item1->menu_name);
        $this->assertSame(2, $item1->quantity);
        $this->assertSame(20000, $item1->unit_price);
        $this->assertSame(40000, $item1->subtotal);

        $item2 = $order->items->firstWhere('menu_id', $menu2->id);
        $this->assertNotNull($item2);
        $this->assertSame('Butter Croissant', $item2->menu_name);
        $this->assertSame(1, $item2->quantity);
        $this->assertSame(15000, $item2->unit_price);
        $this->assertSame(15000, $item2->subtotal);

        // Session cart must be cleared
        $this->assertEmpty(session('cart', []));
    }
}
