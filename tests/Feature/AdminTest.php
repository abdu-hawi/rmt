<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\ProductSeeder::class);
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->admin = User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);
});

it('redirects guests from admin', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('allows authenticated admin to access dashboard', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200);
});

it('lists products in admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.products.index'))
        ->assertStatus(200)
        ->assertSee('Riof ERP');
});

it('lists orders in admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.orders.index'))
        ->assertStatus(200);
});

it('lists customers in admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.customers.index'))
        ->assertStatus(200)
        ->assertSee($this->admin->name);
});

it('lists coupons in admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.coupons.index'))
        ->assertStatus(200);
});

it('shows settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.settings.index'))
        ->assertStatus(200)
        ->assertSee('Riof Digital Store');
});

it('creates a product via admin', function () {
    $this->actingAs($this->admin)->post(route('admin.products.store'), [
        'name_en' => 'Test Product EN',
        'name_ar' => 'Test Product AR',
        'description_en' => 'English description',
        'description_ar' => 'Arabic description',
        'price_usd' => 99.99,
        'price_sar' => 374.96,
        'is_active' => true,
    ])->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', ['name_en' => 'Test Product EN']);
});

it('persists the tax-inclusive flag when creating a product', function () {
    $this->actingAs($this->admin)->post(route('admin.products.store'), [
        'name_en' => 'Tax Inclusive Product',
        'name_ar' => 'منتج شامل الضريبة',
        'description_en' => 'English description',
        'description_ar' => 'Arabic description',
        'price_usd' => 100.00,
        'price_sar' => 375.00,
        'is_tax_inclusive' => '1',
        'is_active' => true,
    ])->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', [
        'name_en' => 'Tax Inclusive Product',
        'is_tax_inclusive' => true,
    ]);
});

it('pre-checks the tax-inclusive flag on the edit form and can clear it', function () {
    $product = \App\Models\Product::first();
    $product->update(['is_tax_inclusive' => true]);

    $this->actingAs($this->admin)
        ->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('name="is_tax_inclusive" value="1" checked', false);

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name_en' => $product->name_en,
        'name_ar' => $product->name_ar,
        'description_en' => $product->description_en,
        'description_ar' => $product->description_ar,
        'price_usd' => $product->price_usd,
        'price_sar' => $product->price_sar,
        'is_active' => true,
    ])->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->is_tax_inclusive)->toBeFalse();
});

it('renders the arabic tax-inclusive label in the admin product form', function () {
    app()->setLocale('ar');

    $this->actingAs($this->admin)
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('السعر شامل الضريبة');
});
