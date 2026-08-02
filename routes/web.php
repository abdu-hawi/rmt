<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentGatewayController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/processing/{order_number}', [CheckoutController::class, 'processing'])->name('checkout.processing');
Route::get('/checkout/processing/{order_number}/status', [PaymentGatewayController::class, 'checkPaymentStatusAjax'])->name('checkout.payment.status');

Route::get('/order/{orderNumber}', [OrderController::class, 'confirmation'])->name('orders.confirmation');
Route::post('/order/lookup', [OrderController::class, 'lookup'])->name('orders.lookup');

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');
Route::get('/currency/{currency}', [LanguageController::class, 'currency'])->name('currency.switch');

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::post('pay_callback', [PaymentGatewayController::class, 'handleCallback'])->name('edfapay.callback');
Route::match(['GET', 'POST'], 'edfapay-success', [PaymentGatewayController::class, 'paymentSuccess'])->name('edfapay.success');
Route::match(['GET', 'POST'], 'edfapay-failed', [PaymentGatewayController::class, 'paymentFailed'])->name('edfapay.failed');

require __DIR__.'/admin.php';
