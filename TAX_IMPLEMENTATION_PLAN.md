# Tax-Inclusive / Tax-Exclusive Pricing — Implementation Plan

> Status: **completed** — all steps implemented, tested and verified.

## 1. Objective

Support two price modes per product and calculate VAT correctly across the storefront, cart,
checkout and admin order screens:

- **Tax exclusive** (`is_tax_inclusive = false`, default): VAT is **added on top** of the price.
- **Tax inclusive** (`is_tax_inclusive = true`): VAT is **extracted from** the price.
- **Mixed carts** (both modes at once) must produce cent-exact totals:
  `Subtotal (Excl. VAT) − Discount + VAT = Grand Total`.
- Orders must store a **snapshot** (flag + VAT + net amount per line) so later product edits never
  change historical orders.

## 2. Before / after

| Area | Before | After |
|---|---|---|
| `products` | `price_usd`, `price_sar` | + `is_tax_inclusive` (boolean, default `false`) |
| Cart session item | `id, slug, name, price_usd, quantity` | + `is_tax_inclusive` snapshot |
| `CartService` | one VAT rule (always add 15% on the discounted subtotal) | `summary()` engine: per-line tax mode, mixed carts, discount allocation |
| `order_items` | `price` (unit), `quantity` | + `is_tax_inclusive`, `net_total`, `tax_amount` |
| Storefront | price only | `Tax Included` / `شامل الضريبة` badge for inclusive products |
| Admin orders | unit price × qty | unit price, net (excl. VAT), VAT, line total, per-line tax mode badge |

## 3. Architecture decisions

1. **One calculation engine** — `CartService::summary(?string $currency): array` is the single source
   of truth. `subtotal()`, `discount()`, `taxableSubtotal()`, `vat()` and `total()` delegate to it
   (memoised per request/currency and invalidated on every cart mutation), so no screen can drift
   from another and coupon lookups are not repeated (N+1 safe).
2. **Line-level rounding first, totals last** — each line is rounded to 2 decimals; totals are sums of
   rounded lines, guaranteeing `Subtotal + VAT == Grand Total` with zero cent drift.
3. **Per-line rounding rules**
   - Exclusive: `net = price × qty` → `tax = round(net × rate, 2)` → `gross = net + tax`.
   - Inclusive: `gross = price × qty` → `net = round(gross / (1 + rate), 2)` →
     `tax = gross − net` → `gross` stays exactly the price the customer saw.
4. **Discounts stay on the tax-exclusive base** (existing ZATCA behaviour and existing tests): the
   coupon amount is computed from the net subtotal, then allocated proportionally to each line's net
   share with the rounding remainder on the last line (`Σ line discounts == coupon amount`). Tax is
   re-derived on the discounted base; a coupon can never exceed the cart subtotal.
5. **Snapshot at checkout** — `order_items` keeps `is_tax_inclusive`, `net_total` and `tax_amount`.
   Historical rows are back-filled (`net_total = price × qty`, `tax_amount` = proportional share of
   `orders.vat`, flag `false`) so old orders keep a coherent breakdown.
6. **Currency first, then tax** — unit prices are converted (`CurrencyService::convert`, 2 decimals),
   then tax maths runs in the active currency (cart, checkout, order and admin stay consistent).
7. **Gateway currency** — EdfaPay is documented as SAR-only (`PROJECT_MAP.md`); the checkout converts
   the grand total to SAR when the order currency is USD and always sends `SAR` to the gateway. This
   also fixes the previously red `CheckoutTest` case.
8. **Translations** — every new label goes through `__()` with keys in `lang/en.json` + `lang/ar.json`
   (`Tax Included` → `شامل الضريبة`, `Price includes Tax` → `السعر شامل الضريبة`, …). The site default
   locale is Arabic, so the Arabic labels from the brief render automatically.

## 4. Formulas

`r` = `config('tax.vat_rate')` (0.15).

### Tax exclusive
```
net        = price × qty
tax        = round(net × r, 2)
line total = net + tax
```

### Tax inclusive
```
gross      = price × qty
net        = round(gross / (1 + r), 2)
tax        = gross − net          ← exactly the requirement (price − base)
line total = gross
```

### Mixed cart
```
Subtotal          = Σ net_i
Discount          = coupon amount, capped at Subtotal
AllocatedDiscount = round(Discount × net_i / Subtotal, 2) (+ remainder on the last line)
NetAfter_i        = net_i − AllocatedDiscount_i
Tax_i             = exclusive ? round(NetAfter_i × r, 2) : grossAfter_i − NetAfter_i
LineTotal_i       = NetAfter_i + Tax_i
VAT               = Σ Tax_i
GrandTotal        = Σ LineTotal_i = Subtotal − Discount + VAT   (exact, cent-safe)
```

### Worked examples (rate 15%)
| Cart | Subtotal | Discount | VAT | Grand total |
|---|---|---|---|---|
| Exclusive 100 × 1 | 100.00 | 0 | 15.00 | 115.00 |
| Inclusive 115 × 1 | 100.00 | 0 | 15.00 | 115.00 |
| Inclusive 102 × 1 | 88.70 | 0 | **13.30** (not 13.31) | **102.00** |
| Mixed 100 (excl) + 115 (incl) | 200.00 | 0 | 30.00 | 230.00 |
| Mixed + 10% coupon | 200.00 | 20.00 | 27.00 | 207.00 |
| Inclusive 450,000 SAR (live check) | 391,304.35 | 0 | 58,695.65 | **450,000.00** |

## 5. Database changes

| Migration | Table | Change |
|---|---|---|
| `2026_11_18_000002_add_is_tax_inclusive_to_products_table.php` | `products` | `is_tax_inclusive` BOOLEAN default `false` |
| `2026_11_18_000003_add_tax_snapshot_to_order_items_table.php` | `order_items` | `is_tax_inclusive` BOOLEAN default `false`, `net_total` DECIMAL(14,2) default `0`, `tax_amount` DECIMAL(14,2) default `0` + back-fill of existing rows |

Both migrations were run successfully (`php artisan migrate`).

## 6. Files touched

### Database
- [x] `database/migrations/2026_11_18_000002_add_is_tax_inclusive_to_products_table.php` (new)
- [x] `database/migrations/2026_11_18_000003_add_tax_snapshot_to_order_items_table.php` (new)
- [x] Migrations run

### Models
- [x] `app/Models/Product.php` — `is_tax_inclusive` fillable + `boolean` cast
- [x] `app/Models/OrderItem.php` — new fillables, decimal/boolean casts, `gross_total` accessor

### Domain logic
- [x] `app/Services/CartService.php` — flag snapshot on add; `summary()` engine (mixed carts,
      proportional discount allocation, remainder handling, coupon cap); memoisation + invalidation

### Controllers
- [x] `app/Http/Controllers/Admin/ProductController.php` — validate + persist `is_tax_inclusive`
- [x] `app/Http/Controllers/CheckoutController.php` — totals from `summary()`, per-line snapshot,
      SAR-only gateway amount

### Views — admin
- [x] `resources/views/admin/products/create.blade.php` — “السعر شامل الضريبة” checkbox + hint
- [x] `resources/views/admin/products/edit.blade.php` — same checkbox, restored from `old()`/model
- [x] `resources/views/admin/orders/show.blade.php` — unit price, net, VAT, qty, line total,
      `Tax Included` / `Tax Excluded` badge + explanatory note

### Views — storefront
- [x] `resources/views/products/partials/card.blade.php` — badge (home / category / search / pagination)
- [x] `resources/views/products/show.blade.php` — badge on the price block + related products
- [x] `resources/views/cart/index.blade.php` — per-line badge
- [x] `resources/views/checkout/index.blade.php` — per-line badge
- [x] `resources/views/orders/confirmation.blade.php` — per-line net/VAT/total + tax mode badge

### Translations
- [x] `lang/en.json`, `lang/ar.json` — `Tax Included`, `Tax Excluded`, `Price includes Tax`, `VAT`,
      `Net (Excl. VAT)`, `Unit Price` + helper texts

### Tests
- [x] `tests/Feature/TaxPricingTest.php` (new, 16 tests) — exclusive, inclusive, inexact inclusive
      extraction (102.00), mixed cart, percentage/fixed coupons, oversized coupon clamp,
      rounding fuzz across prices/quantities, SAR conversion, storefront/cart/checkout badges,
      Arabic badge, checkout snapshot + immutability, admin order breakdown, confirmation breakdown
- [x] `tests/Feature/AdminTest.php` — flag persisted on create, pre-checked on edit, cleared on update,
      Arabic label rendered
- [x] `tests/Feature/CheckoutTest.php` — SAR-only gateway assertion (documented behaviour) restored
      with an exact amount check
- [x] `tests/Feature/CartTest.php` — untouched, still green

### Docs
- [x] `TAX_IMPLEMENTATION_PLAN.md` (this file)
- [x] `PROJECT_MAP.md` — new milestone entry

## 7. Edge cases covered

- [x] Inclusive price with inexact extraction (`102.00 → 88.70 + 13.30`): grand total stays 102.00.
- [x] Mixed cart invariant `subtotal − discount + vat == total` verified on every scenario.
- [x] Percentage **and** fixed coupons on mixed carts; allocated discounts sum exactly to the coupon.
- [x] Coupon larger than the cart → capped at the net subtotal, no negative lines/tax.
- [x] Heavy discount on an inclusive line → gross clamped to the net base (VAT never negative).
- [x] Multiple quantities (rounding is per line, not per unit).
- [x] SAR conversion before tax; live check: 450,000 SAR → 391,304.35 + 58,695.65.
- [x] Historical orders: new columns back-filled; legacy cart items default to exclusive.
- [x] Changing a product after checkout does not alter stored order items (tested).
- [x] Unchecked checkbox updates an inclusive product back to exclusive (tested).

## 8. Verification

```
php artisan migrate          # both migrations OK
php artisan test             # 76 tests, 263 assertions — all green
npm run build                # vite build OK
```

Live smoke test on `http://rmt.test` (product temporarily flagged inclusive, then reverted):

- product card + product page show `شامل الضريبة`
- cart: subtotal `391,304.35` + VAT `58,695.65` = total `450,000.00` (exact)
- admin order screen renders the per-line tax breakdown (covered by test)

## 9. Progress log

- [x] Codebase exploration (schema, models, services, controllers, views, tests)
- [x] Plan documented
- [x] Migrations + models
- [x] CartService engine
- [x] Admin (controller + forms)
- [x] Checkout + order snapshot
- [x] Storefront badges + cart/checkout/confirmation views
- [x] Translations
- [x] Tests written & green
- [x] Build verified
