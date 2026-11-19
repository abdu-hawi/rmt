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

    /** @var array<string, array<string, mixed>> */
    private array $summaryCache = [];

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
                'price_sar' => (float) $product->price_sar,
                'is_tax_inclusive' => (bool) $product->is_tax_inclusive,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::CART_KEY, $cart);
        $this->flushSummary();
    }

    public function remove(int $productId): void
    {
        $cart = Session::get(self::CART_KEY, []);
        unset($cart[$productId]);
        Session::put(self::CART_KEY, $cart);
        $this->flushSummary();
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
        $this->flushSummary();
    }

    public function clear(): void
    {
        Session::forget([self::CART_KEY, self::COUPON_KEY]);
        $this->flushSummary();
    }

    public function count(): int
    {
        return array_sum(array_column(Session::get(self::CART_KEY, []), 'quantity'));
    }

    /**
     * Cent-exact breakdown of the cart (per line + totals).
     *
     * Tax-exclusive lines add VAT on top of the price, tax-inclusive lines
     * extract it from the price. Every line is rounded to 2 decimals and the
     * totals are sums of those rounded lines, so:
     * Subtotal (excl. VAT) - Discount + VAT === Grand Total, always.
     */
    public function summary(?string $currency = null): array
    {
        $currency = $currency ?? CurrencyService::current();

        if (isset($this->summaryCache[$currency])) {
            return $this->summaryCache[$currency];
        }

        $rate = (float) config('tax.vat_rate', 0.15);
        $lines = [];
        $subtotal = 0.0;

        foreach (Session::get(self::CART_KEY, []) as $item) {
            // Use the price stored for the active currency; converting between
            // currencies here caused cent drift (e.g. 200,000 → 199,999.99).
            $unitPrice = $currency === CurrencyService::SAR
                ? (float) ($item['price_sar'] ?? CurrencyService::convert((float) $item['price_usd'], CurrencyService::SAR))
                : (float) $item['price_usd'];
            $quantity = (int) $item['quantity'];
            $gross = round($unitPrice * $quantity, 2);
            $isInclusive = (bool) ($item['is_tax_inclusive'] ?? false);
            $net = $isInclusive ? round($gross / (1 + $rate), 2) : $gross;

            $lines[] = [
                'id' => (int) $item['id'],
                'slug' => $item['slug'] ?? null,
                'name' => $item['name'],
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'is_tax_inclusive' => $isInclusive,
                'gross' => $gross,
                'net' => $net,
                'discount' => 0.0,
                'net_total' => $net,
                'tax_amount' => 0.0,
                'total' => $gross,
            ];

            $subtotal += $net;
        }

        $subtotal = round($subtotal, 2);
        $discount = $this->couponDiscount($subtotal);

        if ($discount > 0.0 && $lines !== []) {
            $this->allocateDiscount($lines, $discount, $subtotal);
        }

        $taxTotal = 0.0;
        $grandTotal = 0.0;

        foreach ($lines as &$line) {
            $netTotal = $line['net_total'];

            if ($line['is_tax_inclusive']) {
                $grossTotal = round(max($netTotal, $line['gross'] - round($line['discount'] * (1 + $rate), 2)), 2);
                $tax = round($grossTotal - $netTotal, 2);
            } else {
                $tax = round($netTotal * $rate, 2);
                $grossTotal = round($netTotal + $tax, 2);
            }

            $line['tax_amount'] = $tax;
            $line['total'] = $grossTotal;

            $taxTotal += $tax;
            $grandTotal += $grossTotal;
        }
        unset($line);

        return $this->summaryCache[$currency] = [
            'currency' => $currency,
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount' => round($discount, 2),
            'taxable_subtotal' => round($subtotal - $discount, 2),
            'vat' => round($taxTotal, 2),
            'total' => round($grandTotal, 2),
        ];
    }

    public function subtotal(?string $currency = null): float
    {
        return $this->summary($currency)['subtotal'];
    }

    public function discount(?string $currency = null): float
    {
        return $this->summary($currency)['discount'];
    }

    public function taxableSubtotal(?string $currency = null): float
    {
        return $this->summary($currency)['taxable_subtotal'];
    }

    public function vat(?string $currency = null): float
    {
        return $this->summary($currency)['vat'];
    }

    public function total(?string $currency = null): float
    {
        return $this->summary($currency)['total'];
    }

    /**
     * Coupon amount, always expressed on the tax-exclusive base and capped so
     * a coupon can never exceed the cart subtotal.
     */
    private function couponDiscount(float $subtotal): float
    {
        $coupon = $this->appliedCoupon();

        if (!$coupon || $subtotal <= 0.0) {
            return 0.0;
        }

        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return 0.0;
        }

        $discount = $coupon->type === 'percentage'
            ? round($subtotal * $coupon->value / 100, 2)
            : (float) $coupon->value;

        return round(max(0.0, min($discount, $subtotal)), 2);
    }

    /**
     * Split the coupon amount across lines proportionally to their net share.
     * The rounding remainder is assigned to the last line so the sum of the
     * allocated discounts equals the coupon amount exactly.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function allocateDiscount(array &$lines, float $discount, float $subtotal): void
    {
        $remaining = $discount;
        $lastIndex = count($lines) - 1;

        foreach ($lines as $index => &$line) {
            if ($subtotal <= 0.0) {
                $lineDiscount = 0.0;
            } elseif ($index === $lastIndex) {
                $lineDiscount = min($remaining, $line['net']);
            } else {
                $lineDiscount = min(round($line['net'] * $discount / $subtotal, 2), $line['net'], $remaining);
            }

            $line['discount'] = round($lineDiscount, 2);
            $line['net_total'] = round($line['net'] - $line['discount'], 2);
            $remaining = round($remaining - $line['discount'], 2);
        }
        unset($line);
    }

    private function flushSummary(): void
    {
        $this->summaryCache = [];
    }

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon || !$coupon->isValid()) {
            return ['success' => false, 'message' => __('Invalid coupon')];
        }
        Session::put(self::COUPON_KEY, $coupon->id);
        $this->flushSummary();
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
        $this->flushSummary();
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
