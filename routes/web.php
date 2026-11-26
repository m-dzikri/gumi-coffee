<?php

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

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

require __DIR__.'/auth.php';
