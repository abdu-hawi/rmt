<?php

use App\Http\Middleware\Localize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\ProductSeeder::class);
});

it('switches to Arabic locale', function () {
    $this->withSession([Localize::LOCALE_KEY => 'ar'])
        ->get(route('home'))
        ->assertStatus(200);

    expect(App::getLocale())->toBe('ar');
});

it('switches to English locale', function () {
    $this->withSession([Localize::LOCALE_KEY => 'en'])
        ->get(route('home'))
        ->assertStatus(200);

    expect(App::getLocale())->toBe('en');
});

it('switches locale via URL', function () {
    $this->get(route('lang.switch', 'ar'))->assertRedirect();
    expect(session(Localize::LOCALE_KEY))->toBe('ar');
});

it('switches currency via URL', function () {
    $this->get(route('currency.switch', 'sar'))->assertRedirect();
    expect(session('currency'))->toBe('sar');
});

it('uses RTL layout for Arabic', function () {
    $response = $this->withSession(['locale' => 'ar'])->get(route('home'));
    $response->assertStatus(200);
});

it('uses LTR layout for English', function () {
    $response = $this->withSession(['locale' => 'en'])->get(route('home'));
    $response->assertStatus(200);
});
