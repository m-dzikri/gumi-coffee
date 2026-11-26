<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $validStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PREPARING,
            Order::STATUS_READY,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ];

        $statusParam = $request->query('status');
        $activeStatus = is_string($statusParam) && in_array($statusParam, $validStatuses, true)
            ? $statusParam
            : null;

        $searchParam = $request->query('search');
        $search = is_string($searchParam) ? trim($searchParam) : '';

        $query = Order::query()
            ->with(['user', 'items'])
            ->orderByDesc('id');

        if ($activeStatus !== null) {
            $query->where('status', $activeStatus);
        }

        if ($search !== '') {
            $query->whereRaw('LOWER(order_code) LIKE ?', ['%'.strtolower($search).'%']);
        }

        $orders = $query->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'validStatuses' => $validStatuses,
            'activeStatus' => $activeStatus,
            'search' => $search,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function markPaid(Order $order): RedirectResponse
    {
        $order->markCashPaidByAdmin();

        return redirect()
            ->back(fallback: route('admin.orders.show', $order))
            ->with('status', "Pembayaran tunai pesanan {$order->order_code} berhasil ditandai lunas (PAID).");
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $order->transitionStatusByAdmin($validated['status']);

        return redirect()
            ->back(fallback: route('admin.orders.show', $order))
            ->with('status', "Status pesanan {$order->order_code} berhasil diperbarui menjadi ".strtoupper($order->status).'.');
    }
}
