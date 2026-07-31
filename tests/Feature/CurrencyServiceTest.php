<?php

use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    session()->put('currency', 'usd');
});

it('defaults to USD', function () {
    expect(CurrencyService::current())->toBe('usd');
    expect(CurrencyService::symbol())->toBe('$');
    expect(CurrencyService::name())->toBe('USD');
});

it('switches currency', function () {
    CurrencyService::switch('sar');
    expect(CurrencyService::current())->toBe('sar');
    expect(CurrencyService::symbol())->toBe('ر.س');
    expect(CurrencyService::name())->toBe('SAR');
});

it('rejects invalid currency', function () {
    CurrencyService::switch('eur');
    expect(CurrencyService::current())->not->toBe('eur');
});

it('converts USD to SAR', function () {
    $sar = CurrencyService::convert(100, 'sar');
    expect($sar)->toBe(375.0);
});

it('formats USD price', function () {
    expect(CurrencyService::format(99.99, 'usd'))->toBe('$99.99');
});

it('formats SAR price', function () {
    expect(CurrencyService::format(375.0, 'sar'))->toBe('375.00 ر.س');
});

it('returns available currencies', function () {
    $available = CurrencyService::available();
    expect($available)->toHaveKeys(['usd', 'sar']);
    expect($available['usd'])->toBe('USD');
    expect($available['sar'])->toBe('SAR');
});
