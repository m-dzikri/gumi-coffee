<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(User $customer, array $overrides = []): Order
    {
        $order = Order::query()->create(array_merge([
            'order_code' => null,
            'user_id' => $customer->id,
            'total' => 25000,
            'status' => Order::STATUS_PENDING,
            'payment_method' => Order::METHOD_CASH,
            'payment_status' => Order::PAYMENT_UNPAID,
            'paid_at' => null,
            'notes' => null,
        ], $overrides));

        if (! $order->order_code) {
            $order->order_code = 'GM-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
            $order->save();
        }

        $order->items()->create([
            'menu_id' => null,
            'menu_name' => 'Kopi Susu Gumi',
            'quantity' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
        ]);

        return $order;
    }

    public function test_ac1_admin_can_list_filter_by_status_and_search_orders_case_insensitively_while_customers_are_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $pendingOrder = $this->createOrder($customer, [
            'order_code' => 'GM-0010',
            'status' => Order::STATUS_PENDING,
        ]);

        $preparingOrder = $this->createOrder($customer, [
            'order_code' => 'GM-0042',
            'status' => Order::STATUS_PREPARING,
        ]);

        $readyOrder = $this->createOrder($customer, [
            'order_code' => 'GM-0099',
            'status' => Order::STATUS_READY,
        ]);

        // 1. Customer access to all /admin/orders* endpoints is rejected with 403
        $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.orders.show', $preparingOrder))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.orders.mark-paid', $pendingOrder))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.orders.update-status', $pendingOrder), [
            'status' => Order::STATUS_PREPARING,
        ])->assertForbidden();

        // 2. Admin can access GET /admin/orders and see all orders
        $allResponse = $this->actingAs($admin)->get(route('admin.orders.index'));
        $allResponse->assertOk();
        $allResponse->assertSeeText('GM-0010');
        $allResponse->assertSeeText('GM-0042');
        $allResponse->assertSeeText('GM-0099');
        $this->assertSame(1, substr_count((string) $allResponse->getContent(), '<h1'));

        // 3. Filter by status: GET /admin/orders?status=preparing shows only preparing orders
        $filteredResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'preparing']));
        $filteredResponse->assertOk();
        $filteredResponse->assertSeeText('GM-0042');
        $filteredResponse->assertDontSeeText('GM-0010');
        $filteredResponse->assertDontSeeText('GM-0099');

        // 4. Partial search: GET /admin/orders?search=0042 finds GM-0042
        $partialSearchResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['search' => '0042']));
        $partialSearchResponse->assertOk();
        $partialSearchResponse->assertSeeText('GM-0042');
        $partialSearchResponse->assertDontSeeText('GM-0010');
        $partialSearchResponse->assertDontSeeText('GM-0099');

        // 5. Case-insensitive search: GET /admin/orders?search=gm-0042 finds GM-0042
        $caseSearchResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['search' => 'gm-0042']));
        $caseSearchResponse->assertOk();
        $caseSearchResponse->assertSeeText('GM-0042');
        $caseSearchResponse->assertDontSeeText('GM-0010');
        $caseSearchResponse->assertDontSeeText('GM-0099');

        // 6. Admin order detail page renders properly with single h1
        $showResponse = $this->actingAs($admin)->get(route('admin.orders.show', $preparingOrder));
        $showResponse->assertOk();
        $showResponse->assertSeeText('GM-0042');
        $this->assertSame(1, substr_count((string) $showResponse->getContent(), '<h1'));
    }

    public function test_ac2_admin_can_mark_cash_orders_as_paid_only_in_valid_states(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Valid states: cash + unpaid in pending, preparing, and ready
        foreach ([Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY] as $allowedStatus) {
            $cashOrder = $this->createOrder($customer, [
                'payment_method' => Order::METHOD_CASH,
                'payment_status' => Order::PAYMENT_UNPAID,
                'status' => $allowedStatus,
            ]);

            $this->actingAs($admin)
                ->post(route('admin.orders.mark-paid', $cashOrder))
                ->assertSessionHasNoErrors();

            $cashOrder->refresh();
            $this->assertSame(Order::PAYMENT_PAID, $cashOrder->payment_status);
            $this->assertNotNull($cashOrder->paid_at);
        }

        // 2. Rejected when status is cancelled or completed
        foreach ([Order::STATUS_CANCELLED, Order::STATUS_COMPLETED] as $disallowedStatus) {
            $terminalOrder = $this->createOrder($customer, [
                'payment_method' => Order::METHOD_CASH,
                'payment_status' => Order::PAYMENT_UNPAID,
                'status' => $disallowedStatus,
            ]);

            $this->actingAs($admin)
                ->post(route('admin.orders.mark-paid', $terminalOrder))
                ->assertSessionHasErrors('payment_status');

            $terminalOrder->refresh();
            $this->assertSame(Order::PAYMENT_UNPAID, $terminalOrder->payment_status);
            $this->assertNull($terminalOrder->paid_at);
        }

        // 3. Rejected when payment_method is qris_dummy
        $qrisOrder = $this->createOrder($customer, [
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'payment_status' => Order::PAYMENT_UNPAID,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.mark-paid', $qrisOrder))
            ->assertSessionHasErrors('payment_status');

        $this->assertSame(Order::PAYMENT_UNPAID, $qrisOrder->fresh()->payment_status);

        // 4. Rejected when order is already paid
        $alreadyPaidCashOrder = $this->createOrder($customer, [
            'payment_method' => Order::METHOD_CASH,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now()->subMinutes(5),
            'status' => Order::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.mark-paid', $alreadyPaidCashOrder))
            ->assertSessionHasErrors('payment_status');
    }
}
