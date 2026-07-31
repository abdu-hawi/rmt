<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CurrencyService
{
    public const USD = 'usd';
    public const SAR = 'sar';

    public static function getRate(): float
    {
        return (float) config('currency.rate_usd_to_sar', 3.75);
    }

    public static function current(): string
    {
        return Session::get('currency', config('currency.default', self::USD));
    }

    public static function switch(string $currency): void
    {
        if (in_array($currency, [self::USD, self::SAR])) {
            Session::put('currency', $currency);
        }
    }

    public static function symbol(string $currency = null): string
    {
        $currency = $currency ?? self::current();
        return config("currency.symbols.{$currency}", '$');
    }

    public static function name(string $currency = null): string
    {
        $currency = $currency ?? self::current();
        return config("currency.names.{$currency}", 'USD');
    }

    public static function convert(float $amountUsd, string $to = null): float
    {
        $to = $to ?? self::current();
        if ($to === self::SAR) {
            return round($amountUsd * self::getRate(), 2);
        }
        return $amountUsd;
    }

    public static function format(float $amount, string $currency = null): string
    {
        $currency = $currency ?? self::current();
        $symbol = self::symbol($currency);
        if ($currency === self::SAR) {
            return number_format($amount, 2) . ' ' . $symbol;
        }
        return $symbol . number_format($amount, 2);
    }

    public static function available(): array
    {
        return [
            self::USD => config('currency.names.usd', 'USD'),
            self::SAR => config('currency.names.sar', 'SAR'),
        ];
    }
}
