<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
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
