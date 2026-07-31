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
