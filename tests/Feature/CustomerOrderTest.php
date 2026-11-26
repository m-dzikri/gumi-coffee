<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderFor(User $user, array $overrides = []): Order
    {
        $order = Order::query()->create(array_merge([
            'order_code' => null,
            'user_id' => $user->id,
            'total' => 24000,
            'status' => Order::STATUS_PENDING,
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'payment_status' => Order::PAYMENT_UNPAID,
            'paid_at' => null,
            'notes' => 'Catatan test',
        ], $overrides));

        if (! $order->order_code) {
            $order->order_code = 'GM-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
            $order->save();
        }

        $order->items()->create([
            'menu_id' => null,
            'menu_name' => 'Gumi Signature Kopi Susu',
            'quantity' => 1,
            'unit_price' => 24000,
            'subtotal' => 24000,
        ]);

        return $order;
    }

    public function test_ac7_qris_dummy_simulation_display_and_payment_rules(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $qrisOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        // GET /orders/{order} shows SIMULASI label and "Saya sudah bayar" button
        $detailResponse = $this->actingAs($customer)->get(route('orders.show', $qrisOrder));
        $detailResponse->assertOk();
        $detailResponse->assertSeeText('SIMULASI');
        $detailResponse->assertSeeText('Saya sudah bayar');
        $detailResponse->assertSeeText($qrisOrder->order_code);
        $this->assertSame(1, substr_count((string) $detailResponse->getContent(), '<h1'));

        // POST /orders/{order}/pay marks order as paid and populates paid_at
        $payResponse = $this->actingAs($customer)->post(route('orders.pay', $qrisOrder));
        $payResponse->assertRedirect(route('orders.show', $qrisOrder));
        $payResponse->assertSessionHasNoErrors();

        $qrisOrder->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $qrisOrder->payment_status);
        $this->assertNotNull($qrisOrder->paid_at);

        // Calling POST /orders/{order}/pay again when already paid must fail with 'payment_status'
        $this->actingAs($customer)
            ->post(route('orders.pay', $qrisOrder))
            ->assertSessionHasErrors('payment_status');

        // Reject POST /orders/{order}/pay when payment_method = 'cash'
        $cashOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_CASH,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($customer)
            ->post(route('orders.pay', $cashOrder))
            ->assertSessionHasErrors('payment_status');
        $this->assertSame(Order::PAYMENT_UNPAID, $cashOrder->fresh()->payment_status);

        // Reject POST /orders/{order}/pay when status is preparing or cancelled
        foreach ([Order::STATUS_PREPARING, Order::STATUS_CANCELLED] as $nonPendingStatus) {
            $nonPendingOrder = $this->createOrderFor($customer, [
                'payment_method' => Order::METHOD_QRIS_DUMMY,
                'status' => $nonPendingStatus,
                'payment_status' => Order::PAYMENT_UNPAID,
            ]);

            $this->actingAs($customer)
                ->post(route('orders.pay', $nonPendingOrder))
                ->assertSessionHasErrors('payment_status');
            $this->assertSame(Order::PAYMENT_UNPAID, $nonPendingOrder->fresh()->payment_status);
        }
    }

    public function test_ac8_customer_order_cancellation_rules_and_payment_vs_cancel_mutual_exclusion(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Customer can cancel a pending + unpaid order
        $cancellableOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_CASH,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($customer)
            ->post(route('orders.cancel', $cancellableOrder))
            ->assertRedirect(route('orders.show', $cancellableOrder))
            ->assertSessionHasNoErrors();

        $this->assertSame(Order::STATUS_CANCELLED, $cancellableOrder->fresh()->status);

        // 2. Mutual exclusion (G-2 / P1): Once QRIS order is paid, cancellation is rejected with 'status'
        $paidQrisOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($customer)
            ->post(route('orders.pay', $paidQrisOrder))
            ->assertSessionHasNoErrors();

        $this->actingAs($customer)
            ->post(route('orders.cancel', $paidQrisOrder))
            ->assertSessionHasErrors('status');

        $paidQrisOrder->refresh();
        $this->assertSame(Order::STATUS_PENDING, $paidQrisOrder->status);
        $this->assertSame(Order::PAYMENT_PAID, $paidQrisOrder->payment_status);

        // 3. Mutual exclusion (G-2 / P1): Once order is cancelled, QRIS payment is rejected with 'payment_status'
        $cancelledQrisOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $this->actingAs($customer)
            ->post(route('orders.cancel', $cancelledQrisOrder))
            ->assertSessionHasNoErrors();

        $this->actingAs($customer)
            ->post(route('orders.pay', $cancelledQrisOrder))
            ->assertSessionHasErrors('payment_status');

        $cancelledQrisOrder->refresh();
        $this->assertSame(Order::STATUS_CANCELLED, $cancelledQrisOrder->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $cancelledQrisOrder->payment_status);

        // 4. Orders in preparing, ready, or completed cannot be cancelled by customer
        foreach ([Order::STATUS_PREPARING, Order::STATUS_READY, Order::STATUS_COMPLETED] as $advancedStatus) {
            $advancedOrder = $this->createOrderFor($customer, [
                'payment_method' => Order::METHOD_CASH,
                'status' => $advancedStatus,
                'payment_status' => Order::PAYMENT_UNPAID,
            ]);

            $this->actingAs($customer)
                ->post(route('orders.cancel', $advancedOrder))
                ->assertSessionHasErrors('status');

            $this->assertSame($advancedStatus, $advancedOrder->fresh()->status);
        }
    }

    public function test_ac9_order_policy_isolates_orders_between_customers(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);

        $orderA = $this->createOrderFor($customerA, [
            'order_code' => 'GM-0101',
            'payment_method' => Order::METHOD_QRIS_DUMMY,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $orderB = $this->createOrderFor($customerB, [
            'order_code' => 'GM-0202',
            'payment_method' => Order::METHOD_CASH,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        // Customer B cannot view, pay, or cancel Customer A's order (403 Forbidden)
        $this->actingAs($customerB)
            ->get(route('orders.show', $orderA))
            ->assertForbidden();

        $this->actingAs($customerB)
            ->post(route('orders.pay', $orderA))
            ->assertForbidden();

        $this->actingAs($customerB)
            ->post(route('orders.cancel', $orderA))
            ->assertForbidden();

        // Order A remains untouched
        $orderA->refresh();
        $this->assertSame(Order::STATUS_PENDING, $orderA->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $orderA->payment_status);

        // GET /orders for Customer B only displays Customer B's order
        $historyResponse = $this->actingAs($customerB)->get(route('orders.index'));
        $historyResponse->assertOk();
        $historyResponse->assertSeeText('GM-0202');
        $historyResponse->assertDontSeeText('GM-0101');
    }

    public function test_centralized_order_state_machine_methods_enforce_admin_payment_and_transition_matrix(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Cash order lifecycle: pending (unpaid) -> preparing -> ready -> markCashPaidByAdmin -> completed
        $cashOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_CASH,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $cashOrder->transitionStatusByAdmin(Order::STATUS_PREPARING);
        $this->assertSame(Order::STATUS_PREPARING, $cashOrder->status);

        $cashOrder->transitionStatusByAdmin(Order::STATUS_READY);
        $this->assertSame(Order::STATUS_READY, $cashOrder->status);

        // Cannot complete while unpaid
        try {
            $cashOrder->transitionStatusByAdmin(Order::STATUS_COMPLETED);
            $this->fail('Expected ValidationException when completing unpaid cash order.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        // Mark cash paid by admin, then complete
        $cashOrder->markCashPaidByAdmin();
        $this->assertSame(Order::PAYMENT_PAID, $cashOrder->payment_status);
        $this->assertNotNull($cashOrder->paid_at);

        $cashOrder->transitionStatusByAdmin(Order::STATUS_COMPLETED);
        $this->assertSame(Order::STATUS_COMPLETED, $cashOrder->status);

        // 2. Unclaimed cash order at ready + unpaid can be cancelled by admin (deadlock prevention)
        $unclaimedCashOrder = $this->createOrderFor($customer, [
            'payment_method' => Order::METHOD_CASH,
            'status' => Order::STATUS_READY,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $unclaimedCashOrder->transitionStatusByAdmin(Order::STATUS_CANCELLED);
        $this->assertSame(Order::STATUS_CANCELLED, $unclaimedCashOrder->status);
    }
}
