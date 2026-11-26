<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        /** @var array<int|string, array{menu_id: int, menu_name?: string, quantity: int}> $cart */
        $cart = $request->session()->get('cart', []);

        $menus = Menu::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $cartItems = [];
        $total = 0;

        foreach ($cart as $menuId => $item) {
            $intId = (int) $menuId;
            /** @var Menu|null $menu */
            $menu = $menus->get($intId);
            $quantity = (int) ($item['quantity'] ?? 1);
            $unitPrice = $menu ? (int) $menu->price : 0;
            $subtotal = $menu ? ($unitPrice * $quantity) : 0;
            $isAvailable = $menu !== null && $menu->is_available;

            if ($isAvailable) {
                $total += $subtotal;
            }

            $cartItems[] = [
                'menu_id' => $intId,
                'menu_name' => $menu ? $menu->name : ($item['menu_name'] ?? "Menu #{$intId}"),
                'category' => $menu?->category ?? 'Menu',
                'image_path' => $menu?->image_path,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'is_available' => $isAvailable,
                'exists' => $menu !== null,
            ];
        }

        return view('cart.index', [
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
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $menuId = (int) $validated['menu_id'];
        $addQuantity = (int) ($validated['quantity'] ?? 1);

        $menu = Menu::query()->findOrFail($menuId);

        if (! $menu->is_available) {
            throw ValidationException::withMessages([
                'cart' => "Menu {$menu->name} sedang tidak tersedia.",
            ]);
        }

        /** @var array<int, array{menu_id: int, menu_name: string, quantity: int}> $cart */
        $cart = $request->session()->get('cart', []);

        $existingQuantity = isset($cart[$menuId]) ? (int) $cart[$menuId]['quantity'] : 0;
        $newQuantity = $existingQuantity + $addQuantity;

        if ($newQuantity > 99) {
            throw ValidationException::withMessages([
                'quantity' => 'Total kuantitas satu menu di keranjang tidak boleh melebihi 99.',
            ]);
        }

        $cart[$menuId] = [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => $newQuantity,
        ];

        $request->session()->put('cart', $cart);

        return redirect()->back()->with('status', "{$menu->name} berhasil ditambahkan ke keranjang.");
    }

    public function update(Request $request, int $menuId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        /** @var array<int, array{menu_id: int, menu_name?: string, quantity: int}> $cart */
        $cart = $request->session()->get('cart', []);

        if (! isset($cart[$menuId])) {
            throw ValidationException::withMessages([
                'cart' => 'Item tidak ditemukan di dalam keranjang belanja.',
            ]);
        }

        $menu = Menu::query()->find($menuId);

        if (! $menu || ! $menu->is_available) {
            $name = $menu ? $menu->name : ($cart[$menuId]['menu_name'] ?? "Menu #{$menuId}");

            throw ValidationException::withMessages([
                'cart' => "Menu {$name} sedang tidak tersedia.",
            ]);
        }

        $cart[$menuId] = [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => (int) $validated['quantity'],
        ];

        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', "Kuantitas {$menu->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, int $menuId): RedirectResponse
    {
        /** @var array<int, array{menu_id: int, menu_name?: string, quantity: int}> $cart */
        $cart = $request->session()->get('cart', []);
        $removedName = $cart[$menuId]['menu_name'] ?? 'Item';

        unset($cart[$menuId]);

        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', "{$removedName} dihapus dari keranjang.");
    }
}
