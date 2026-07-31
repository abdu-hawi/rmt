<?php

use App\Services\CurrencyService;

if (!function_exists('currency')) {
    function currency(): CurrencyService
    {
        return app(CurrencyService::class);
    }
}

if (!function_exists('format_price')) {
    function format_price(float $amountUsd, string $currency = null): string
    {
        $currency = $currency ?? CurrencyService::current();
        $converted = CurrencyService::convert($amountUsd, $currency);
        return CurrencyService::format($converted, $currency);
    }
}

if (!function_exists('active_locale')) {
    function active_locale(): string
    {
        return app()->getLocale();
    }
}

if (!function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return app()->getLocale() === 'ar';
    }
}
