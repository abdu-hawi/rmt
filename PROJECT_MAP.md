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
| **Logging** | Laravel Monolog (async queue recommended for prod) |
| **Testing** | Pest 4.x |
| **Packages Installed** | `laravel/framework ^13.8`, `laravel/tinker ^3.0`, `laravel/pail ^1.2.5`, `pestphp/pest ^4.7` |

**System Date (verified)**: 2026-07-30  
**PHP Version**: 8.3.14 ✓  
**Composer Version**: 2.8.10 ✓  
**Laravel Version**: 13.23.0 ✓

---

## [SYSTEM_FLOW]

```
User (Guest or Registered)
  │
  ├─► Storefront (Blade + Tailwind)
  │     ├─ Product Catalog (filtering, search)
  │     ├─ Product Detail (SEO meta, JSON-LD, dual pricing)
  │     ├─ Cart (session-based)
  │     └─ Checkout (guest/registered, payer fields)
  │           └─ Order Confirmation
  │
  ├─► Currency Switcher (USD / SAR) — top-bar toggle
  │
  ├─► Language Switcher (English / Arabic) — RTL/LTR flip
  │
  └─► Admin Panel (Back-Office)
        ├─ Dashboard (analytics)
        ├─ Products (CRUD, digital files, specs)
        ├─ Orders (status, invoices, download links)
        ├─ Customers
        ├─ Coupons
        ├─ Settings (currency rate, SEO defaults)
        └─ Language / Currency management
```

---

## [ARCHITECTURE]

### Directory Structure (Domain-Driven)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── OrderController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── CouponController.php
│   │   │   └── SettingController.php
│   │   ├── ProductController.php        (public)
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── OrderController.php          (public order lookup)
│   │   └── LanguageController.php
│   ├── Middleware/
│   │   ├── Localize.php                 (switches app locale)
│   │   └── Currency.php                 (switches currency)
│   └── Requests/
│       ├── CheckoutRequest.php
│       └── Admin/ProductRequest.php
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Category.php
│   ├── Coupon.php
│   └── Setting.php
├── Services/
│   ├── CurrencyService.php
│   ├── CartService.php
│   └── SeoService.php                  (OG, Schema.org, canonical)
└── Helpers/
    └── helpers.php                      (global utility functions)

config/
├── currency.php                         (USD/SAR rate, symbols)
└── seo.php                              (default OG settings)

lang/
├── en.json
└── ar.json

resources/views/
├── layouts/
│   ├── app.blade.php                    (public master with LTR)
│   ├── app-rtl.blade.php               (public master with RTL)
│   └── admin.blade.php                  (admin master)
├── components/                          (reusable Blade components)
├── products/
├── cart/
├── checkout/
├── admin/
│   ├── dashboard/
│   ├── products/
│   ├── orders/
│   ├── customers/
│   ├── coupons/
│   └── settings/
└── seo/                                 (partials for meta/JSON-LD)

routes/
├── web.php                              (public routes)
└── admin.php                            (admin prefix routes)
```

### Database Schema (Core Tables)

```
products
├── id
├── slug (unique)
├── type (string: 'digital')
├── name_en, name_ar
├── description_en, description_ar
├── price_usd (decimal 14,2)
├── price_sar (decimal 14,2)
├── features_en (json), features_ar (json)
├── seo_title_en, seo_title_ar
├── seo_description_en, seo_description_ar
├── seo_keywords_en, seo_keywords_ar
├── schema_type (string: 'SoftwareApplication' etc.)
├── is_active (bool)
├── sort_order (int)
├── created_at, updated_at

orders
├── id
├── order_number (string, unique)
├── user_id (nullable, FK)
├── payer_first_name
├── payer_last_name
├── payer_address
├── payer_country
├── payer_city
├── payer_email
├── payer_phone
├── payer_zip
├── currency (enum: usd, sar)
├── subtotal (decimal 14,2)
├── discount (decimal 14,2)
├── total (decimal 14,2)
├── status (enum: pending, completed, cancelled)
├── coupon_id (nullable, FK)
├── notes (nullable)
├── created_at, updated_at

order_items
├── id
├── order_id (FK)
├── product_id (FK)
├── product_name (snapshot)
├── price (decimal 14,2)
├── quantity (int, default 1)
├── created_at

categories (multi-level)
├── id
├── parent_id (nullable, self FK)
├── slug
├── name_en, name_ar
├── is_active (bool)

coupons
├── id
├── code (unique)
├── type (enum: percentage, fixed)
├── value (decimal 14,2)
├── min_order_amount (decimal 14,2, nullable)
├── max_uses (int, nullable)
├── used_count (int)
├── expires_at (nullable)
├── is_active (bool)

settings
├── id
├── key (unique)
├── value (text)
├── created_at, updated_at
```

---

## [ORPHANS & PENDING]

> No orphans yet. Project is at Milestone 0.

### Verifiable Milestones

| # | Milestone | Deliverable | Verification |
|---|-----------|-------------|--------------|
| 1 | Planning Approval | PROJECT_MAP.md + Assumptions doc | User sign-off |
| 2 | Database Schema | All migrations | `php artisan migrate` |
| 3 | Localization & SEO | lang/ files, SeoService, helpers | Translation unit tests |
| 4 | Models & Relationships | All Eloquent models | `php artisan tinker` inspect |
| 5 | Seeders | 4 products (EN/AR) with full SEO | `php artisan db:seed` |
| 6 | Currency & Cart Services | CurrencyService, CartService | Unit tests |
| 7 | Public Routes + Controllers | Products, Cart, Checkout | `php artisan route:list` |
| 8 | Admin Panel | Full CRUD for all entities | Manual walkthrough |
| 9 | Checkout Flow | Guest + Registered checkout | E2E test |
| 10 | Integration Test Suite | Pest tests for core paths | `php artisan test` |

---

*Last updated: 2026-07-30*
