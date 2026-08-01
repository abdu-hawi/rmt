<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const CART_KEY = 'cart';
    private const COUPON_KEY = 'cart_coupon';

    public function items(): Collection
    {
        return collect(Session::get(self::CART_KEY, []));
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = Session::get(self::CART_KEY, []);
        $id = $product->id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en,
                'price_usd' => (float) $product->price_usd,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::CART_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = Session::get(self::CART_KEY, []);
        unset($cart[$productId]);
        Session::put(self::CART_KEY, $cart);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = Session::get(self::CART_KEY, []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = max(0, $quantity);
            if ($cart[$productId]['quantity'] <= 0) {
                unset($cart[$productId]);
            }
        }
        Session::put(self::CART_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget([self::CART_KEY, self::COUPON_KEY]);
    }

    public function count(): int
    {
        return array_sum(array_column(Session::get(self::CART_KEY, []), 'quantity'));
    }

    public function subtotal(string $currency = null): float
    {
        $currency = $currency ?? CurrencyService::current();
        $total = 0;
        foreach (Session::get(self::CART_KEY, []) as $item) {
            $price = $currency === CurrencyService::SAR
                ? CurrencyService::convert($item['price_usd'], CurrencyService::SAR)
                : $item['price_usd'];
            $total += $price * $item['quantity'];
        }
        return round($total, 2);
    }

    public function discount(string $currency = null): float
    {
        $coupon = $this->appliedCoupon();
        if (!$coupon) {
            return 0;
        }
        $subtotal = $this->subtotal($currency);
        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return 0;
        }
        if ($coupon->type === 'percentage') {
            return round($subtotal * $coupon->value / 100, 2);
        }
        return min($coupon->value, $subtotal);
    }

    public function taxableSubtotal(string $currency = null): float
    {
        return round(max(0, $this->subtotal($currency) - $this->discount($currency)), 2);
    }

    public function vat(string $currency = null): float
    {
        return round($this->taxableSubtotal($currency) * config('tax.vat_rate', 0.15), 2);
    }

    public function total(string $currency = null): float
    {
        return round($this->taxableSubtotal($currency) + $this->vat($currency), 2);
    }

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon || !$coupon->isValid()) {
            return ['success' => false, 'message' => __('Invalid coupon')];
        }
        Session::put(self::COUPON_KEY, $coupon->id);
        return ['success' => true, 'message' => __('Coupon applied')];
    }

    public function appliedCoupon(): ?Coupon
    {
        $id = Session::get(self::COUPON_KEY);
        if (!$id) {
            return null;
        }
        return Coupon::find($id);
    }

    public function removeCoupon(): void
    {
        Session::forget(self::COUPON_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
