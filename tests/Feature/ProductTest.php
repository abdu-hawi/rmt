<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('en');
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\ProductSeeder::class);
});

it('lists all products on the home page', function () {
    $response = $this->get(route('home'));
    $response->assertStatus(200);
    $response->assertSee('Riof ERP');
    $response->assertSee('Riof Store v-1');
    $response->assertSee('Riof HR v-1');
    $response->assertSee('Learning Management System CMS');
});

it('shows a single product page', function () {
    $product = Product::where('slug', 'riof-erp')->first();
    $response = $this->get(route('products.show', $product->slug));
    $response->assertStatus(200);
    $response->assertSee($product->name_en);
    $response->assertSee($product->description_en);
});

it('returns 404 for inactive product', function () {
    $product = Product::first();
    $product->update(['is_active' => false]);
    $this->get(route('products.show', $product->slug))->assertStatus(404);
});

it('searches products by name', function () {
    $response = $this->get(route('products.index', ['search' => 'ERP']));
    $response->assertStatus(200);
    $response->assertSee('Riof ERP');
});

it('filters products by category', function () {
    $category = Category::where('slug', 'enterprise-software')->first();
    $response = $this->get(route('products.index', ['category' => $category->slug]));
    $response->assertStatus(200);
    $response->assertSee('Riof ERP');
});

it('includes schema JSON-LD on product page', function () {
    $product = Product::first();
    $response = $this->get(route('products.show', $product->slug));
    $response->assertSee('application/ld+json');
    $response->assertSee('SoftwareApplication');
});

it('offers a one-click add-to-cart form on every product card', function () {
    $response = $this->get(route('home'))->assertOk();
    $html = $response->getContent();

    $expectedCards = min(Product::where('is_active', true)->count(), 12); // 12 = listing page size

    expect(substr_count($html, 'data-ajax-add="1"'))->toBe($expectedCards)
        ->and(substr_count($html, 'name="product_id"'))->toBe($expectedCards)
        ->and($html)->toContain('action="' . route('cart.add') . '"')
        ->and($html)->toContain('add-to-cart-btn');
});

it('shows the exact stored price for the active currency without cent drift', function () {
    $product = Product::create([
        'slug' => 'price-drift-product',
        'type' => 'digital',
        'name_en' => 'Price Drift Product',
        'name_ar' => 'منتج اختبار السعر',
        'description_en' => 'Test description',
        'description_ar' => 'وصف الاختبار',
        'price_usd' => 53333.33,
        'price_sar' => 200000.00,
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('200,000.00')
        ->assertDontSee('199,999.99');

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('200,000.00')
        ->assertDontSee('199,999.99');
});

it('shows the stored price of the selected currency', function () {
    $product = Product::create([
        'slug' => 'price-currency-product',
        'type' => 'digital',
        'name_en' => 'Price Currency Product',
        'name_ar' => 'منتج العملة',
        'description_en' => 'Test description',
        'description_ar' => 'وصف الاختبار',
        'price_usd' => 46666.67,
        'price_sar' => 175000.00,
        'is_active' => true,
    ]);

    session()->put('currency', 'usd');

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('$46,666.67');
});
