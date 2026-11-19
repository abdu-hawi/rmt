<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('en');

    $this->seed(\Database\Seeders\CategorySeeder::class);

    $this->cart = app(CartService::class);
    $this->cart->clear();

    $this->makeProduct = function (float $priceUsd, bool $inclusive = false, array $overrides = []): Product {
        return Product::create(array_merge([
            'slug' => 'tax-test-' . uniqid(),
            'type' => 'digital',
            'category_id' => Category::first()?->id,
            'name_en' => 'Tax Test Product',
            'name_ar' => 'منتج اختبار الضريبة',
            'description_en' => 'Test description',
            'description_ar' => 'وصف الاختبار',
            'price_usd' => $priceUsd,
            'price_sar' => round($priceUsd * 3.75, 2),
            'is_active' => true,
            'is_tax_inclusive' => $inclusive,
        ], $overrides));
    };
});

it('adds VAT on top of tax-exclusive prices', function () {
    $product = ($this->makeProduct)(100.00, false);

    $this->cart->add($product, 2);
    $summary = $this->cart->summary('usd');

    expect($summary['subtotal'])->toBe(200.00)
        ->and($summary['discount'])->toBe(0.00)
        ->and($summary['vat'])->toBe(30.00)
        ->and($summary['total'])->toBe(230.00)
        ->and($summary['lines'][0]['is_tax_inclusive'])->toBeFalse()
        ->and($summary['lines'][0]['unit_price'])->toBe(100.00);
});

it('extracts VAT from tax-inclusive prices', function () {
    $product = ($this->makeProduct)(115.00, true);

    $this->cart->add($product, 1);
    $summary = $this->cart->summary('usd');

    expect($summary['subtotal'])->toBe(100.00)
        ->and($summary['vat'])->toBe(15.00)
        ->and($summary['total'])->toBe(115.00)
        ->and($summary['lines'][0]['is_tax_inclusive'])->toBeTrue();
});

it('keeps the inclusive price intact when the extracted VAT is not a whole cent', function () {
    $product = ($this->makeProduct)(102.00, true);

    $this->cart->add($product, 1);
    $summary = $this->cart->summary('usd');

    // Base = 102 / 1.15 = 88.6956 → 88.70, Tax = 102 - 88.70 = 13.30 (not 13.31)
    expect($summary['subtotal'])->toBe(88.70)
        ->and($summary['vat'])->toBe(13.30)
        ->and($summary['total'])->toBe(102.00)
        ->and(round($summary['subtotal'] + $summary['vat'], 2))->toBe($summary['total']);
});

it('calculates mixed inclusive and exclusive carts exactly', function () {
    $exclusive = ($this->makeProduct)(100.00, false);
    $inclusive = ($this->makeProduct)(115.00, true);

    $this->cart->add($exclusive, 1);
    $this->cart->add($inclusive, 2);

    $summary = $this->cart->summary('usd');

    // 100 net + 15 VAT (exclusive) | 230 gross → 200 net + 30 VAT (inclusive)
    expect($summary['subtotal'])->toBe(300.00)
        ->and($summary['vat'])->toBe(45.00)
        ->and($summary['total'])->toBe(345.00)
        ->and(round(array_sum(array_column($summary['lines'], 'net_total')), 2))->toBe($summary['subtotal'])
        ->and(round(array_sum(array_column($summary['lines'], 'tax_amount')), 2))->toBe($summary['vat'])
        ->and(round(array_sum(array_column($summary['lines'], 'total')), 2))->toBe($summary['total'])
        ->and(round($summary['subtotal'] - $summary['discount'] + $summary['vat'], 2))->toBe($summary['total']);
});

it('applies a percentage coupon to mixed carts without cent drift', function () {
    $exclusive = ($this->makeProduct)(100.00, false);
    $inclusive = ($this->makeProduct)(115.00, true);

    $this->cart->add($exclusive, 1);
    $this->cart->add($inclusive, 1);

    Coupon::create(['code' => 'MIX10', 'type' => 'percentage', 'value' => 10]);
    $this->cart->applyCoupon('MIX10');

    $summary = $this->cart->summary('usd');

    expect($summary['subtotal'])->toBe(200.00)
        ->and($summary['discount'])->toBe(20.00)
        ->and($summary['taxable_subtotal'])->toBe(180.00)
        ->and($summary['vat'])->toBe(27.00)
        ->and($summary['total'])->toBe(207.00)
        ->and(round(array_sum(array_column($summary['lines'], 'discount')), 2))->toBe($summary['discount'])
        ->and(round($summary['subtotal'] - $summary['discount'] + $summary['vat'], 2))->toBe($summary['total']);
});

it('applies a fixed coupon to mixed carts exactly', function () {
    $exclusive = ($this->makeProduct)(100.00, false);
    $inclusive = ($this->makeProduct)(115.00, true);

    $this->cart->add($exclusive, 1);
    $this->cart->add($inclusive, 1);

    Coupon::create(['code' => 'MIXFIX', 'type' => 'fixed', 'value' => 10]);
    $this->cart->applyCoupon('MIXFIX');

    $summary = $this->cart->summary('usd');

    expect($summary['discount'])->toBe(10.00)
        ->and($summary['taxable_subtotal'])->toBe(190.00)
        ->and($summary['vat'])->toBe(28.50)
        ->and($summary['total'])->toBe(218.50)
        ->and(round($summary['subtotal'] - $summary['discount'] + $summary['vat'], 2))->toBe($summary['total']);
});

it('never produces negative lines when the coupon exceeds the cart', function () {
    $product = ($this->makeProduct)(30.00, true);

    $this->cart->add($product, 1);

    Coupon::create(['code' => 'HUGE', 'type' => 'fixed', 'value' => 1000]);
    $this->cart->applyCoupon('HUGE');

    $summary = $this->cart->summary('usd');

    expect($summary['discount'])->toBe($summary['subtotal'])
        ->and($summary['taxable_subtotal'])->toBe(0.00)
        ->and($summary['vat'])->toBe(0.00)
        ->and($summary['total'])->toBe(0.00)
        ->and($summary['lines'][0]['net_total'])->toBe(0.00)
        ->and($summary['lines'][0]['tax_amount'])->toBe(0.00);
});

it('keeps totals cent-exact across price and quantity combinations', function () {
    $prices = [0.01, 0.03, 0.07, 1.16, 10.01, 33.33, 99.99, 102.00, 999.99, 120000.00];

    foreach ($prices as $price) {
        $this->cart->clear();

        $exclusive = ($this->makeProduct)($price, false);
        $inclusive = ($this->makeProduct)($price, true);

        $this->cart->add($exclusive, 3);
        $this->cart->add($inclusive, 2);

        $summary = $this->cart->summary('usd');

        expect(round(array_sum(array_column($summary['lines'], 'total')), 2))
            ->toBe($summary['total'], "line totals drifted at price {$price}")
            ->and(round($summary['subtotal'] - $summary['discount'] + $summary['vat'], 2))
            ->toBe($summary['total'], "cart totals drifted at price {$price}");

        $inclusiveLine = $summary['lines'][1];
        expect($inclusiveLine['total'])
            ->toBe(round($inclusiveLine['unit_price'] * $inclusiveLine['quantity'], 2),
                "inclusive line did not keep the catalog price at {$price}");
    }
});

it('uses the stored SAR price for tax calculations', function () {
    $product = ($this->makeProduct)(100.00, true); // stored SAR price = 375.00 (inclusive)

    $this->cart->add($product, 1);
    $summary = $this->cart->summary('sar');

    expect($summary['currency'])->toBe('sar')
        ->and($summary['total'])->toBe(375.00)
        ->and($summary['subtotal'])->toBe(326.09)
        ->and($summary['vat'])->toBe(48.91)
        ->and(round($summary['subtotal'] + $summary['vat'], 2))->toBe(375.00);
});

it('stores the exact stored SAR price on order items', function () {
    Http::fake([
        'api.edfapay.com/payment/initiate' => Http::response([
            'redirect_url' => 'https://checkout.edfapay.test/pay/888',
        ], 200),
    ]);

    Redis::shouldReceive('connection')->with('payments_conn')->andReturn(
        Mockery::mock(['setex' => true])
    );

    config([
        'services.edfapay.merchant_id' => 'test_merchant',
        'services.edfapay.merchant_password' => 'test_secret',
    ]);

    session()->put('currency', 'sar');

    // Admin typed 200,000 SAR while the USD price was derived from the rate.
    $product = ($this->makeProduct)(53333.33, false, ['price_sar' => 200000.00]);

    $this->cart->add($product, 1);

    $this->post(route('checkout.store'), [
        'payer_first_name' => 'John', 'payer_last_name' => 'Doe',
        'payer_address' => '123 St', 'payer_country' => 'SA',
        'payer_city' => 'Riyadh', 'payer_email' => 'sar@test.com',
        'payer_phone' => '123', 'payer_zip' => '10001',
    ]);

    $order = Order::with('items')->firstOrFail();
    $item = $order->items->first();

    expect((float) $order->subtotal)->toBe(200000.0)
        ->and((float) $order->vat)->toBe(30000.0)
        ->and((float) $order->total)->toBe(230000.0)
        ->and((float) $item->price)->toBe(200000.0)
        ->and((float) $item->net_total)->toBe(200000.0)
        ->and((float) $item->tax_amount)->toBe(30000.0);
});

it('shows the tax badge only for tax-inclusive products', function () {
    $inclusive = ($this->makeProduct)(115.00, true, ['name_en' => 'Inclusive Widget', 'slug' => 'inclusive-widget']);
    $exclusive = ($this->makeProduct)(100.00, false, ['name_en' => 'Exclusive Widget', 'slug' => 'exclusive-widget', 'category_id' => null]);

    $listing = $this->get(route('products.index'))->assertOk();

    expect(substr_count($listing->getContent(), 'Tax Included'))->toBe(1);

    $this->get(route('products.show', $inclusive->slug))
        ->assertOk()
        ->assertSee('Tax Included');

    $this->get(route('products.show', $exclusive->slug))
        ->assertOk()
        ->assertDontSee('Tax Included');
});

it('shows the badge in Arabic on the storefront', function () {
    app()->setLocale('ar');

    $inclusive = ($this->makeProduct)(115.00, true, ['name_en' => 'Arabic Widget', 'slug' => 'arabic-widget']);

    $this->get(route('products.show', $inclusive->slug))
        ->assertOk()
        ->assertSee('شامل الضريبة');
});

it('shows the tax badge in the cart and checkout summary', function () {
    $inclusive = ($this->makeProduct)(115.00, true);
    $exclusive = ($this->makeProduct)(100.00, false);

    session()->put('currency', 'usd');

    $this->cart->add($inclusive, 1);
    $this->cart->add($exclusive, 1);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Tax Included')
        ->assertSee('Subtotal (Excl. VAT)')
        ->assertSee('Grand Total');

    $this->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('Tax Included')
        ->assertSee('VAT (15%)');
});

it('renders the tax breakdown on the admin order screen', function () {
    $admin = \App\Models\User::factory()->create([
        'name' => 'Admin',
        'email' => 'tax-admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $order = Order::create([
        'order_number' => 'ADMINTAX1234',
        'payer_first_name' => 'John', 'payer_last_name' => 'Doe',
        'payer_address' => 'St', 'payer_country' => 'SA',
        'payer_city' => 'Riyadh', 'payer_email' => 'admin-tax@test.com',
        'payer_phone' => '123', 'payer_zip' => '10001',
        'currency' => 'usd', 'subtotal' => 200, 'discount' => 0, 'vat' => 30, 'total' => 230,
    ]);

    $order->items()->create([
        'product_id' => ($this->makeProduct)(115.00, true)->id,
        'product_name' => 'Admin Inclusive Item',
        'price' => 115.00,
        'quantity' => 1,
        'is_tax_inclusive' => true,
        'net_total' => 100.00,
        'tax_amount' => 15.00,
    ]);

    $order->items()->create([
        'product_id' => ($this->makeProduct)(100.00, false)->id,
        'product_name' => 'Admin Exclusive Item',
        'price' => 100.00,
        'quantity' => 1,
        'is_tax_inclusive' => false,
        'net_total' => 100.00,
        'tax_amount' => 15.00,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Admin Inclusive Item')
        ->assertSee('Admin Exclusive Item')
        ->assertSee('Tax Included')
        ->assertSee('Tax Excluded')
        ->assertSee('Net (Excl. VAT)');
});

it('stores the tax snapshot on order items at checkout', function () {
    Http::fake([
        'api.edfapay.com/payment/initiate' => Http::response([
            'redirect_url' => 'https://checkout.edfapay.test/pay/777',
        ], 200),
    ]);

    Redis::shouldReceive('connection')->with('payments_conn')->andReturn(
        Mockery::mock(['setex' => true])
    );

    config([
        'services.edfapay.merchant_id' => 'test_merchant',
        'services.edfapay.merchant_password' => 'test_secret',
    ]);

    $inclusive = ($this->makeProduct)(115.00, true);
    $exclusive = ($this->makeProduct)(100.00, false);

    session()->put('currency', 'usd');

    $this->cart->add($inclusive, 1);
    $this->cart->add($exclusive, 1);

    $this->post(route('checkout.store'), [
        'payer_first_name' => 'John', 'payer_last_name' => 'Doe',
        'payer_address' => '123 St', 'payer_country' => 'SA',
        'payer_city' => 'Riyadh', 'payer_email' => 'john@test.com',
        'payer_phone' => '123', 'payer_zip' => '10001',
    ]);

    $order = Order::with('items')->firstOrFail();

    expect((float) $order->subtotal)->toBe(200.00)
        ->and((float) $order->vat)->toBe(30.00)
        ->and((float) $order->total)->toBe(230.00);

    $inclusiveItem = $order->items->firstWhere('product_id', $inclusive->id);
    $exclusiveItem = $order->items->firstWhere('product_id', $exclusive->id);

    expect($inclusiveItem->is_tax_inclusive)->toBeTrue()
        ->and((float) $inclusiveItem->net_total)->toBe(100.00)
        ->and((float) $inclusiveItem->tax_amount)->toBe(15.00)
        ->and($inclusiveItem->gross_total)->toBe(115.00)
        ->and($exclusiveItem->is_tax_inclusive)->toBeFalse()
        ->and((float) $exclusiveItem->net_total)->toBe(100.00)
        ->and((float) $exclusiveItem->tax_amount)->toBe(15.00)
        ->and($exclusiveItem->gross_total)->toBe(115.00);

    // Snapshot must survive later product edits.
    $inclusive->update(['is_tax_inclusive' => false, 'price_usd' => 999.00]);

    $storedItem = $order->fresh()->items->firstWhere('product_id', $inclusive->id);
    expect($storedItem->is_tax_inclusive)->toBeTrue()
        ->and((float) $storedItem->tax_amount)->toBe(15.00)
        ->and((float) $storedItem->net_total)->toBe(100.00);
});

it('renders the order confirmation breakdown for mixed carts', function () {
    $order = Order::create([
        'order_number' => 'MIXEDTAX1234',
        'payer_first_name' => 'John', 'payer_last_name' => 'Doe',
        'payer_address' => 'St', 'payer_country' => 'SA',
        'payer_city' => 'Riyadh', 'payer_email' => 'mix@test.com',
        'payer_phone' => '123', 'payer_zip' => '10001',
        'currency' => 'usd', 'subtotal' => 200, 'discount' => 0, 'vat' => 30, 'total' => 230,
    ]);

    $order->items()->create([
        'product_id' => ($this->makeProduct)(115.00, true)->id,
        'product_name' => 'Inclusive Item',
        'price' => 115.00,
        'quantity' => 1,
        'is_tax_inclusive' => true,
        'net_total' => 100.00,
        'tax_amount' => 15.00,
    ]);

    $order->items()->create([
        'product_id' => ($this->makeProduct)(100.00, false)->id,
        'product_name' => 'Exclusive Item',
        'price' => 100.00,
        'quantity' => 1,
        'is_tax_inclusive' => false,
        'net_total' => 100.00,
        'tax_amount' => 15.00,
    ]);

    $this->get(route('orders.confirmation', $order->order_number))
        ->assertOk()
        ->assertSee('Inclusive Item')
        ->assertSee('Exclusive Item')
        ->assertSee('Tax Included')
        ->assertSee('Tax Excluded');
});
