<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->orderByDesc('id')
            ->get();

        return view('orders.index', [
            'orders' => $orders,
            'estimatedPickupText' => config('gumi.estimated_pickup_text', 'Estimasi siap 10-15 menit'),
            'location' => config('gumi.location', 'Jl. Braga No. 42, Bandung'),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items');

        return view('orders.show', [
            'order' => $order,
            'estimatedPickupText' => config('gumi.estimated_pickup_text', 'Estimasi siap 10-15 menit'),
            'location' => config('gumi.location', 'Jl. Braga No. 42, Bandung'),
            'operatingHours' => config('gumi.operating_hours', ['open' => '08:00', 'close' => '22:00']),
        ]);
    }

    public function pay(Order $order): RedirectResponse
    {
        $order->payQrisByCustomer();

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Pembayaran simulasi QRIS untuk pesanan {$order->order_code} berhasil dikonfirmasi.");
    }

    public function cancel(Order $order): RedirectResponse
    {
        $order->cancelByCustomer();

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Pesanan {$order->order_code} telah dibatalkan.");
    }
}
