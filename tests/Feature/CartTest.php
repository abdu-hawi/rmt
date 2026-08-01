<?php

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('en');
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\ProductSeeder::class);
    $this->cart = app(CartService::class);
    $this->cart->clear();
});

it('adds product to cart', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertRedirect();

    expect($this->cart->count())->toBe(1);
});

it('increases quantity when adding same product', function () {
    $product = Product::first();
    $this->cart->add($product, 1);
    $this->cart->add($product, 2);

    expect($this->cart->count())->toBe(3);
});

it('removes product from cart', function () {
    $product = Product::first();
    $this->cart->add($product, 1);

    $this->post(route('cart.remove', $product->id))->assertRedirect();
    expect($this->cart->isEmpty())->toBeTrue();
});

it('calculates cart total in USD', function () {
    $product = Product::first();
    $this->cart->add($product, 2);

    expect($this->cart->subtotal('usd'))->toBe($product->price_usd * 2);
});

it('calculates VAT after discount per ZATCA', function () {
    $product = Product::first();
    $this->cart->add($product, 1);

    $subtotal = $this->cart->subtotal('usd');
    expect($this->cart->taxableSubtotal('usd'))->toBe($subtotal);
    expect($this->cart->vat('usd'))->toBe(round($subtotal * 0.15, 2));
    expect($this->cart->total('usd'))->toBe(round($subtotal * 1.15, 2));
});

it('applies discount before calculating VAT', function () {
    $product = Product::first();
    $this->cart->add($product, 2);
    $subtotal = $this->cart->subtotal('usd');

    $coupon = \App\Models\Coupon::create([
        'code' => 'ZATCA10',
        'type' => 'percentage',
        'value' => 10,
    ]);
    $this->cart->applyCoupon('ZATCA10');

    $discount = round($subtotal * 0.10, 2);
    $taxable = round($subtotal - $discount, 2);
    $vat = round($taxable * 0.15, 2);

    expect($this->cart->discount('usd'))->toBe($discount);
    expect($this->cart->taxableSubtotal('usd'))->toBe($taxable);
    expect($this->cart->vat('usd'))->toBe($vat);
    expect($this->cart->total('usd'))->toBe(round($taxable + $vat, 2));
});

it('shows cart page', function () {
    $this->get(route('cart.index'))->assertStatus(200);
});

it('shows empty cart message', function () {
    $this->get(route('cart.index'))->assertSee('Your cart is empty');
});
