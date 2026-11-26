<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(User $customer, array $overrides = []): Order
    {
        $order = Order::query()->create(array_merge([
            'order_code' => null,
            'user_id' => $customer->id,
            'total' => 30000,
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

        return $order;
    }

    public function test_ac3_sequential_forward_transitions_succeed_and_skips_or_backward_transitions_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Sequential forward transitions: pending -> preparing -> ready -> completed on a paid order
        $order = $this->createOrder($customer, [
            'status' => Order::STATUS_PENDING,
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        foreach ([Order::STATUS_PREPARING, Order::STATUS_READY, Order::STATUS_COMPLETED] as $nextStatus) {
            $this->actingAs($admin)
                ->patch(route('admin.orders.update-status', $order), ['status' => $nextStatus])
                ->assertSessionHasNoErrors();

            $this->assertSame($nextStatus, $order->fresh()->status);
        }

        // 2. Status skips and backward transitions must fail with validation error 'status'
        $invalidTransitions = [
            // Forward skips
            [Order::STATUS_PENDING, Order::STATUS_READY],
            [Order::STATUS_PENDING, Order::STATUS_COMPLETED],
            [Order::STATUS_PREPARING, Order::STATUS_COMPLETED],
            // Backward transitions
            [Order::STATUS_READY, Order::STATUS_PREPARING],
            [Order::STATUS_READY, Order::STATUS_PENDING],
            [Order::STATUS_COMPLETED, Order::STATUS_READY],
            [Order::STATUS_COMPLETED, Order::STATUS_PREPARING],
            [Order::STATUS_CANCELLED, Order::STATUS_PENDING],
            [Order::STATUS_CANCELLED, Order::STATUS_PREPARING],
        ];

        foreach ($invalidTransitions as [$fromStatus, $toStatus]) {
            $testOrder = $this->createOrder($customer, [
                'status' => $fromStatus,
                'payment_method' => Order::METHOD_CASH,
                'payment_status' => Order::PAYMENT_PAID,
                'paid_at' => now(),
            ]);

            $this->actingAs($admin)
                ->patch(route('admin.orders.update-status', $testOrder), ['status' => $toStatus])
                ->assertSessionHasErrors('status');

            $this->assertSame($fromStatus, $testOrder->fresh()->status);
        }
    }

    public function test_ac4_payment_prerequisites_for_preparing_and_completed_transitions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. pending -> preparing is ALLOWED for unpaid cash order
        $unpaidCashOrder = $this->createOrder($customer, [
            'status' => Order::STATUS_PENDING,
            'payment_method' => Order::METHOD_CASH,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $unpaidCashOrder), ['status' => Order::STATUS_PREPARING])
            ->assertSessionHasNoErrors();

        $this->assertSame(Order::STATUS_PREPARING, $unpaidCashOrder->fresh()->status);

        // 2. pending -> preparing is REJECTED for unpaid qris_dummy order
        $unpaidQrisOrder = $this->createOrder($customer, [
            'status' => Order::STATUS_PENDING,
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $unpaidQrisOrder), ['status' => Order::STATUS_PREPARING])
            ->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_PENDING, $unpaidQrisOrder->fresh()->status);

        // 3. ready -> completed is REJECTED if payment_status is still unpaid (e.g. unpaid cash order)
        $readyUnpaidCashOrder = $this->createOrder($customer, [
            'status' => Order::STATUS_READY,
            'payment_method' => Order::METHOD_CASH,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $readyUnpaidCashOrder), ['status' => Order::STATUS_COMPLETED])
            ->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_READY, $readyUnpaidCashOrder->fresh()->status);
    }

    public function test_ac5_admin_can_cancel_unpaid_orders_including_ready_unclaimed_cash_orders_and_never_paid_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Admin can cancel unpaid orders from pending, preparing, and ready (unclaimed cash order deadlock fix)
        foreach ([Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY] as $cancellableStatus) {
            $unpaidOrder = $this->createOrder($customer, [
                'status' => $cancellableStatus,
                'payment_method' => Order::METHOD_CASH,
                'payment_status' => Order::PAYMENT_UNPAID,
            ]);

            $this->actingAs($admin)
                ->patch(route('admin.orders.update-status', $unpaidOrder), ['status' => Order::STATUS_CANCELLED])
                ->assertSessionHasNoErrors();

            $this->assertSame(Order::STATUS_CANCELLED, $unpaidOrder->fresh()->status);
        }

        // 2. Admin can NEVER cancel a paid order (across pending, preparing, ready, completed)
        foreach ([Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY, Order::STATUS_COMPLETED] as $status) {
            $paidOrder = $this->createOrder($customer, [
                'status' => $status,
                'payment_method' => Order::METHOD_CASH,
                'payment_status' => Order::PAYMENT_PAID,
                'paid_at' => now(),
            ]);

            $this->actingAs($admin)
                ->patch(route('admin.orders.update-status', $paidOrder), ['status' => Order::STATUS_CANCELLED])
                ->assertSessionHasErrors('status');

            $this->assertSame($status, $paidOrder->fresh()->status);
        }
    }
}
