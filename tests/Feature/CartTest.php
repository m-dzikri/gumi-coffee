<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createMenu(array $overrides = []): Menu
    {
        return Menu::query()->create(array_merge([
            'name' => 'Gumi Signature Kopi Susu',
            'slug' => 'gumi-signature-kopi-susu-'.uniqid(),
            'category' => 'Coffee',
            'description' => 'Espresso dengan susu segar dan gula aren.',
            'price' => 24000,
            'image_path' => 'images/menus/kopi-susu-gumi.svg',
            'is_available' => true,
        ], $overrides));
    }

    public function test_ac1_session_cart_operations_and_quantity_validation(): void
    {
        $availableMenu = $this->createMenu([
            'name' => 'Gumi Signature Kopi Susu',
            'price' => 24000,
            'is_available' => true,
        ]);

        $unavailableMenu = $this->createMenu([
            'name' => 'Cold Brew Citrus',
            'price' => 26000,
            'is_available' => false,
        ]);

        // 1. Add available menu to session cart
        $response = $this->post('/cart/items', [
            'menu_id' => $availableMenu->id,
            'quantity' => 2,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, session("cart.{$availableMenu->id}.quantity"));
        $this->assertSame('Gumi Signature Kopi Susu', session("cart.{$availableMenu->id}.menu_name"));

        // 2. Reject quantity = 0 and quantity = 100
        $this->post('/cart/items', [
            'menu_id' => $availableMenu->id,
            'quantity' => 0,
        ])->assertSessionHasErrors('quantity');

        $this->post('/cart/items', [
            'menu_id' => $availableMenu->id,
            'quantity' => 100,
        ])->assertSessionHasErrors('quantity');

        // 3. Reject cumulative quantity > 99 (already 2 in cart + 98 = 100)
        $this->post('/cart/items', [
            'menu_id' => $availableMenu->id,
            'quantity' => 98,
        ])->assertSessionHasErrors('quantity');
        $this->assertSame(2, session("cart.{$availableMenu->id}.quantity"));

        // 4. Reject adding unavailable menu (is_available = false) with 'cart' error key
        $this->post('/cart/items', [
            'menu_id' => $unavailableMenu->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('cart');
        $this->assertNull(session("cart.{$unavailableMenu->id}"));

        // 5. PATCH /cart/items/{menuId} to quantity 3
        $this->patch("/cart/items/{$availableMenu->id}", [
            'quantity' => 3,
        ])->assertRedirect(route('cart.index'));
        $this->assertSame(3, session("cart.{$availableMenu->id}.quantity"));

        // Verify cart page renders server-calculated subtotal and single h1
        $cartPage = $this->get('/cart');
        $cartPage->assertOk();
        $cartPage->assertSeeText('Gumi Signature Kopi Susu');
        $cartPage->assertSeeText('Rp 72.000');
        $this->assertSame(1, substr_count((string) $cartPage->getContent(), '<h1'));

        // 6. DELETE /cart/items/{menuId} works even when menu row has been deleted from menus table
        $deletedMenu = $this->createMenu([
            'name' => 'Menu Sementara',
            'price' => 18000,
            'is_available' => true,
        ]);

        $this->post('/cart/items', [
            'menu_id' => $deletedMenu->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $deletedMenuId = $deletedMenu->id;
        $deletedMenu->delete();

        $deleteResponse = $this->delete("/cart/items/{$deletedMenuId}");
        $deleteResponse->assertRedirect(route('cart.index'));
        $this->assertNull(session("cart.{$deletedMenuId}"));

        // Also delete the first item
        $this->delete("/cart/items/{$availableMenu->id}")
            ->assertRedirect(route('cart.index'));
        $this->assertSame([], session('cart', []));
    }

    public function test_ac1_cart_update_rejects_unavailable_or_missing_items_and_empty_cart_redirects_from_checkout(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $menu = $this->createMenu([
            'name' => 'Latte Dingin',
            'price' => 23000,
            'is_available' => true,
        ]);

        // Updating a menuId not in session('cart') fails with 'cart' error
        $this->patch("/cart/items/{$menu->id}", [
            'quantity' => 2,
        ])->assertSessionHasErrors('cart');

        // Add item to cart, then mark menu unavailable in DB -> PATCH fails with 'cart' error
        $this->post('/cart/items', [
            'menu_id' => $menu->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $menu->update(['is_available' => false]);

        $this->patch("/cart/items/{$menu->id}", [
            'quantity' => 3,
        ])->assertSessionHasErrors('cart');
        $this->assertSame(1, session("cart.{$menu->id}.quantity"));

        // Clear cart and verify GET /checkout redirects to /menus when cart is empty
        $this->delete("/cart/items/{$menu->id}");
        $this->actingAs($customer)
            ->get('/checkout')
            ->assertRedirect(route('menus.index'));
    }

    public function test_ac2_guest_cart_persists_after_login_and_redirects_to_intended_checkout(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@gumicoffee.test',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        $menu = $this->createMenu([
            'name' => 'Matcha Latte',
            'price' => 25000,
            'is_available' => true,
        ]);

        $this->assertGuest();

        // Guest adds item to session cart
        $this->post('/cart/items', [
            'menu_id' => $menu->id,
            'quantity' => 4,
        ])->assertSessionHasNoErrors();

        $this->assertSame(4, session("cart.{$menu->id}.quantity"));

        // Guest accesses GET /checkout -> redirected to /login
        $checkoutAttempt = $this->get('/checkout');
        $checkoutAttempt->assertRedirect(route('login'));

        // Guest logs in -> redirected to intended /checkout with cart preserved
        $loginResponse = $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($customer);
        $loginResponse->assertRedirect(route('checkout.index', absolute: false));
        $this->assertSame(4, session("cart.{$menu->id}.quantity"));
        $this->assertSame('Matcha Latte', session("cart.{$menu->id}.menu_name"));
    }
}
