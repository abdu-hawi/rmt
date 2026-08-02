# PROJECT_MAP — Riof Digital E-Commerce Platform

---

## [TECH_STACK]

| Component | Technology |
|-----------|-----------|
| **Framework** | Laravel 13.23.0 (PHP 8.3.14) |
| **Frontend** | Blade + Tailwind CSS 4 + Vite 8 |
| **Database** | SQLite (dev) / MySQL (prod) |
| **Auth** | Laravel built-in + Guest session-based checkout |
| **Localization** | Laravel `lang/` JSON files (en, ar) |
| **Rendering** | Server-side Blade with RTL/LTR layout switching |
| **Logging** | Laravel Monolog (`daily` channel) |
| **Testing** | Pest 4.x — **56 tests, 130 assertions (all passing)** |

**System Date (verified)**: 2026-07-30  
**PHP**: 8.3.14 ✓ | **Composer**: 2.8.10 ✓ | **Laravel**: 13.23.0 ✓

---

## [SYSTEM_FLOW]

```
User (Guest or Registered)
  │
  ├─► Storefront (Blade + Tailwind)
  │     ├─ Product Catalog (filter, search, category filter)
  │     ├─ Product Detail (SEO meta, JSON-LD, dual pricing, features)
  │     ├─ Cart (session-based, coupon support)
  │     ├─ Checkout (guest/registered, 8 mandatory payer fields)
  │     │     └─ EdfaPay payment gateway (SAR-only, 3DS redirect)
  │     │           └─ Processing page (AJAX poll → Redis → fallback status)
  │     └─ Order Confirmation
  │
  ├─► Currency Switcher (USD / SAR) — top-bar toggle
  ├─► Language Switcher (English / Arabic) — RTL/LTR flip
  │
  └─► Admin Panel (/admin — auth required)
        ├─ Dashboard (analytics: products, orders, revenue, customers)
        ├─ Products (CRUD, dual-lang, SEO fields, categories)
        ├─ Orders (list, detail, status management)
        ├─ Customers (list with order history)
        ├─ Coupons (CRUD, percentage/fixed, expiry, usage limits)
        └─ Settings (store name, description, currency, language, exchange rate)
```

---

## [ARCHITECTURE]

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php (resource)
│   │   │   ├── OrderController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── CouponController.php (resource)
│   │   │   └── SettingController.php
│   │   ├── Auth/
│   │   │   └── LoginController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php  (store → PaymentGatewayController)
│   │   ├── PaymentGatewayController.php (EdfaPay: initiate, callback, status)
│   │   ├── OrderController.php
│   │   └── LanguageController.php
│   ├── Middleware/
│   │   ├── Localize.php          (locale from session)
│   │   ├── CurrencyMiddleware.php (currency from session/query)
│   │   └── AdminMiddleware.php   (auth gate)
│   └── Requests/                 (validation)
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php                 (id → internal relations; order_number → 3rd-party/customer)
│   ├── OrderItem.php
│   ├── PaymentOrder.php          (gateway transactions, belongsTo Order)
│   ├── Category.php
│   ├── Coupon.php
│   └── Setting.php
├── Services/
│   ├── CurrencyService.php       (convert, format, switch)
│   ├── CartService.php           (add, remove, coupon, ZATCA totals)
│   └── SeoService.php            (OG, Schema.org, canonical)
└── Helpers/
    └── helpers.php               (format_price, is_rtl, currency)

config/
├── currency.php                  (rate, symbols, names)
├── tax.php                       (ZATCA VAT 15%)
└── seo.php                       (defaults, site info)

lang/
├── en.json, en/payment_gatways.php
└── ar.json, ar/payment_gatways.php

resources/views/
├── layouts/
│   ├── app.blade.php             (LTR master)
│   └── app-rtl.blade.php         (RTL master)
├── auth/login.blade.php
├── products/index.blade.php, show.blade.php
├── cart/index.blade.php
├── checkout/index.blade.php
├── checkout/processing.blade.php (AJAX poll 2s ×15 → fallback status)
├── orders/confirmation.blade.php
└── admin/
    ├── layouts/admin.blade.php
    ├── dashboard/index.blade.php
    ├── products/index.blade.php, create.blade.php, edit.blade.php
    ├── orders/index.blade.php, show.blade.php
    ├── customers/index.blade.php, show.blade.php
    ├── coupons/index.blade.php, create.blade.php, edit.blade.php
    └── settings/index.blade.php

routes/
├── web.php                       (public routes + auth + EdfaPay + status)
└── admin.php                     (admin prefix, auth+admin middleware)
```

### Database Schema

```
products         → id, slug, type, name_en/ar, description_en/ar, price_usd/sar,
                   features_en/ar (json), seo_* (en/ar), schema_type, is_active,
                   sort_order, download_url, category_id (FK), timestamps

categories       → id, parent_id (self FK), slug, name_en/ar, is_active, sort_order

orders           → id, order_number (unique), user_id (nullable FK),
                   payer_first_name, payer_last_name, payer_address, payer_country,
                   payer_city, payer_email, payer_phone, payer_zip,
                   currency, subtotal, discount, vat, total, status (enum),
                   coupon_id (nullable FK), notes

order_items      → id, order_id (FK), product_id (FK), product_name, price, quantity

payment_orders   → id, order_id (string index → Order::id), trans_id (unique), rrn,
                   action, result, status (pending/completed/failed), amount,
                   card_brand, payload (json), timestamps

coupons          → id, code (unique), type (enum), value, min_order_amount,
                   max_uses, used_count, expires_at, is_active

settings         → id, key (unique), value
```

> **Redis (`payments_conn`)** — Payment Gateway Callback Bridge:
> - `paymentGatewayCallback:{order_id}` → set by `handleCallback`, TTL 18000s, JSON callback payload (uses **internal** `Order::id`)
> - `edfapay_email_{order_number}` → payer email cached for callback hash verification, TTL 86400s

---

## [PAYMENT FLOW (EdfaPay — ZATCA Compliant)]

```
1. CheckoutController::store()
     ├─ validate payer fields (8 mandatory)
     ├─ create Order + OrderItems (totals stored in selected currency)
     ├─ commit transaction + clear cart
     └─ PaymentGatewayController::paymentProcess([...])
           ├─ amount ALWAYS converted to SAR (SAR base for gateway)
           ├─ order_id (payload) = Order::order_number (3rd-party identifier)
           ├─ success_url = route('edfapay.success', ['order_number' => ...])
           └─ redirect to gateway redirect_url (3DS)

2. Gateway → User returns via success_url
     └─ paymentSuccess() → redirect route('checkout.processing', order_number)

3. processing.blade.php (site-identity loading UI, RTL/LTR)
     └─ AJAX poll every 2s (max 15 attempts) → route('checkout.payment.status')
           ├─ server: Redis::payments_conn get "paymentGatewayCallback:{Order::id}"
           │         → if found → status=completed → order marked completed
           └─ after 15 attempts w/ no Redis data → fallback=true
                    → PaymentGatewayController::checkPaymentStatusAjax()
                    → direct paymentStatus() API query (SETTLED → completed)

4. Gateway callback (server-to-server) → route('edfapay.callback')
     └─ verifyEdfaPayCallbackHash() (email + merchant pass + trans + card)
     ├─ PaymentOrder::updateOrCreate(trans_id) — order_id = internal Order::id
     └─ on SALE+SUCCESS+SETTLED → Redis setex "paymentGatewayCallback:{Order::id}"
```

---

## [ORPHANS & PENDING]

> **No orphans.** All milestones from the execution plan have been completed.

### Completed Milestones

| # | Milestone | Status | Verification |
|---|-----------|--------|-------------|
| 1 | Planning & Architecture | ✅ | PROJECT_MAP.md approved |
| 2 | Database Schema (7 migrations) | ✅ | `php artisan migrate` |
| 3 | Eloquent Models + relationships | ✅ | All relations wired |
| 4 | Localization (en.json + ar.json) | ✅ | 130+ translation keys each |
| 5 | Seeders (4 products, EN/AR, SEO) | ✅ | `php artisan db:seed` |
| 6 | Services (Currency, Cart, Seo) | ✅ | Feature-tested |
| 7 | Middleware (Localize, Currency, Admin) | ✅ | Registered in bootstrap/app.php |
| 8 | Routes (43 routes) + Controllers (12) | ✅ | `php artisan route:list` |
| 9 | Blade Views (19 views, RTL/LTR) | ✅ | Full admin panel included |
| 10 | Tests (56 passing, 130 assertions) | ✅ | `php artisan test` |
| 11 | ZATCA VAT Compliance (15%) | ✅ | config/tax.php, orders.vat, CartService taxable/vat, VAT breakdown UI + tests |
| 12 | EdfaPay Payment Integration | ✅ | paymentProcess (SAR-only), callback → Redis → AJAX polling ×15 → fallback status, PaymentOrder + processing page |

---

*Last updated: 2026-08-02 — EdfaPay payment gateway integration completed. All milestones complete. ✅*
