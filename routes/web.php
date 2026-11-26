<?php

use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/items/{menuId}', [CartController::class, 'update'])
    ->whereNumber('menuId')
    ->name('cart.update');
Route::delete('/cart/items/{menuId}', [CartController::class, 'destroy'])
    ->whereNumber('menuId')
    ->name('cart.destroy');

Route::get('/dashboard', function () {
    return redirect()->route('menus.index');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])
        ->middleware('can:view,order')
        ->name('orders.show');
    Route::post('/orders/{order}/pay', [CustomerOrderController::class, 'pay'])
        ->middleware('can:pay,order')
        ->name('orders.pay');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])
        ->middleware('can:cancel,order')
        ->name('orders.cancel');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.orders.index');
    })->name('dashboard');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/mark-paid', [AdminOrderController::class, 'markPaid'])->name('orders.mark-paid');

    Route::get('/menus', [AdminMenuController::class, 'index'])->name('menus.index');
    Route::get('/menus/create', [AdminMenuController::class, 'create'])->name('menus.create');
    Route::post('/menus', [AdminMenuController::class, 'store'])->name('menus.store');
    Route::get('/menus/{menu}/edit', [AdminMenuController::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{menu}', [AdminMenuController::class, 'update'])->name('menus.update');
    Route::patch('/menus/{menu}/toggle-availability', [AdminMenuController::class, 'toggleAvailability'])->name('menus.toggle-availability');
    Route::delete('/menus/{menu}', [AdminMenuController::class, 'destroy'])->name('menus.destroy');
});

require __DIR__.'/auth.php';
