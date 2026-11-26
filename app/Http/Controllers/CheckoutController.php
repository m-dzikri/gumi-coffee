<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        /** @var array<int|string, array{menu_id: int, menu_name?: string, quantity: int}> $cart */
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('menus.index')->with('status', 'Keranjang belanja Anda masih kosong.');
        }

        $menus = Menu::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $cartItems = [];
        $total = 0;

        foreach ($cart as $rawMenuId => $item) {
            $menuId = (int) $rawMenuId;
            /** @var Menu|null $menu */
            $menu = $menus->get($menuId);
            $quantity = (int) ($item['quantity'] ?? 1);
            $unitPrice = $menu ? (int) $menu->price : 0;
            $subtotal = $menu ? ($unitPrice * $quantity) : 0;
            $isAvailable = $menu !== null && $menu->is_available;

            if ($isAvailable) {
                $total += $subtotal;
            }

            $cartItems[] = [
                'menu_id' => $menuId,
                'menu_name' => $menu ? $menu->name : ($item['menu_name'] ?? "Menu #{$menuId}"),
                'category' => $menu?->category ?? 'Menu',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'is_available' => $isAvailable,
            ];
        }

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'operatingHours' => config('gumi.operating_hours', ['open' => '08:00', 'close' => '22:00']),
            'estimatedPickupText' => config('gumi.estimated_pickup_text', 'Estimasi siap 10-15 menit'),
            'location' => config('gumi.location', 'Jl. Braga No. 42, Bandung'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,qris_dummy'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $nowTime = now('Asia/Jakarta')->format('H:i');
        $openTime = (string) config('gumi.operating_hours.open', '08:00');
        $closeTime = (string) config('gumi.operating_hours.close', '22:00');

        if ($nowTime < $openTime || $nowTime > $closeTime) {
            throw ValidationException::withMessages([
                'checkout' => "Pemesanan takeaway hanya dapat dilakukan pada jam operasional kedai ({$openTime} - {$closeTime} WIB).",
            ]);
        }

        $order = DB::transaction(function () use ($request, $validated): Order {
            /** @var array<int|string, array{menu_id: int, menu_name?: string, quantity: int}> $cart */
            $cart = $request->session()->get('cart', []);

            if (empty($cart)) {
                throw ValidationException::withMessages([
                    'checkout' => 'Keranjang belanja Anda kosong.',
                ]);
            }

            $menuIds = array_map('intval', array_keys($cart));
            $menus = Menu::query()
                ->whereIn('id', $menuIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $preparedItems = [];

            foreach ($cart as $rawMenuId => $cartItem) {
                $menuId = (int) $rawMenuId;
                /** @var Menu|null $menu */
                $menu = $menus->get($menuId);

                if (! $menu || ! $menu->is_available) {
                    $name = $menu ? $menu->name : ($cartItem['menu_name'] ?? "Menu #{$menuId}");

                    throw ValidationException::withMessages([
                        'checkout' => "Menu {$name} sedang tidak tersedia.",
                    ]);
                }

                $quantity = (int) ($cartItem['quantity'] ?? 1);
                if ($quantity < 1 || $quantity > 99) {
                    throw ValidationException::withMessages([
                        'checkout' => "Kuantitas untuk {$menu->name} tidak valid.",
                    ]);
                }

                $unitPrice = (int) $menu->price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                $preparedItems[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::query()->create([
                'order_code' => null,
                'user_id' => (int) $request->user()->id,
                'total' => $total,
                'status' => Order::STATUS_PENDING,
                'payment_method' => $validated['payment_method'],
                'payment_status' => Order::PAYMENT_UNPAID,
                'paid_at' => null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->order_code = 'GM-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
            $order->save();

            foreach ($preparedItems as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Pesanan {$order->order_code} berhasil dibuat.");
    }
}
