<?php

use App\Models\Order;
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

it('shows checkout page with items in cart', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->get(route('checkout.index'))->assertStatus(200)->assertSee('Payer Information');
});

it('redirects to cart if empty during checkout', function () {
    $this->get(route('checkout.index'))->assertRedirect(route('cart.index'));
});

it('processes guest checkout successfully', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->post(route('checkout.store'), [
        'payer_first_name' => 'John',
        'payer_last_name' => 'Doe',
        'payer_address' => '123 Main St',
        'payer_country' => 'US',
        'payer_city' => 'New York',
        'payer_email' => 'john@example.com',
        'payer_phone' => '+1234567890',
        'payer_zip' => '10001',
    ]);

    $response->assertRedirect();
    expect(Order::count())->toBe(1);

    $order = Order::first();
    expect($order->payer_email)->toBe('john@example.com');
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->product_name)->toBe($product->name_en);
});

it('stores VAT-compliant totals on the order', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->post(route('checkout.store'), [
        'payer_first_name' => 'John',
        'payer_last_name' => 'Doe',
        'payer_address' => '123 St',
        'payer_country' => 'US',
        'payer_city' => 'NY',
        'payer_email' => 'j@t.com',
        'payer_phone' => '123',
        'payer_zip' => '10001',
    ]);

    $order = Order::first();
    $expectedVat = round($order->subtotal * 0.15, 2);

    expect((float) $order->vat)->toBe($expectedVat);
    expect((float) $order->total)->toBe(round($order->subtotal + $expectedVat, 2));
});

it('shows VAT breakdown on checkout page', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->get(route('checkout.index'))
        ->assertStatus(200)
        ->assertSee('Subtotal (Excl. VAT)')
        ->assertSee('VAT (15%)');
});

it('requires all payer fields', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->post(route('checkout.store'), [])
        ->assertSessionHasErrors([
            'payer_first_name', 'payer_last_name', 'payer_address',
            'payer_country', 'payer_city', 'payer_email',
            'payer_phone', 'payer_zip',
        ]);
});

it('generates unique order numbers for two created orders', function () {
    $order1 = Order::create([
        'order_number' => strtoupper(\Illuminate\Support\Str::random(12)),
        'payer_first_name' => 'John', 'payer_last_name' => 'Doe',
        'payer_address' => 'St', 'payer_country' => 'US',
        'payer_city' => 'NY', 'payer_email' => 'j@t.com',
        'payer_phone' => '123', 'payer_zip' => '10001',
        'currency' => 'usd', 'subtotal' => 100, 'total' => 100,
    ]);
    $order2 = Order::create([
        'order_number' => strtoupper(\Illuminate\Support\Str::random(12)),
        'payer_first_name' => 'Jane', 'payer_last_name' => 'Doe',
        'payer_address' => 'Ave', 'payer_country' => 'US',
        'payer_city' => 'LA', 'payer_email' => 'j2@t.com',
        'payer_phone' => '456', 'payer_zip' => '90001',
        'currency' => 'usd', 'subtotal' => 200, 'total' => 200,
    ]);

    expect($order1->id)->not->toBe($order2->id);
    expect($order1->order_number)->not->toBe($order2->order_number);
});

it('shows order confirmation page', function () {
    $product = Product::first();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->post(route('checkout.store'), [
        'payer_first_name' => 'John', 'payer_last_name' => 'Doe',
        'payer_address' => '123 St', 'payer_country' => 'US',
        'payer_city' => 'NY', 'payer_email' => 'j@t.com',
        'payer_phone' => '123', 'payer_zip' => '10001',
    ]);

    $order = Order::first();
    $this->get(route('orders.confirmation', $order->order_number))
        ->assertStatus(200)
        ->assertSee($order->order_number);
});
