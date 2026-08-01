<?php

use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('en');
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\ProductSeeder::class);
});

it('generates meta tags for a product', function () {
    $product = Product::first();
    $meta = SeoService::metaTags($product);

    expect($meta)->toHaveKeys(['title', 'description', 'keywords', 'canonical', 'og', 'twitter']);
    expect($meta['title'])->toBe($product->seo_title_en);
    expect($meta['og']['locale'])->toBe('en_US');
    expect($meta['twitter']['card'])->toBe('summary_large_image');
});

it('generates Arabic meta tags', function () {
    app()->setLocale('ar');
    $product = Product::first();
    $meta = SeoService::metaTags($product);

    expect($meta['title'])->toBe($product->seo_title_ar);
    expect($meta['og']['locale'])->toBe('ar_SA');
});

it('generates JSON-LD schema for product', function () {
    $product = Product::first();
    $schema = SeoService::schemaJsonLd($product);

    expect($schema)->not->toBeNull();
    expect($schema['@type'])->toBe('SoftwareApplication');
    expect($schema['name'])->toBe($product->name_en);
    expect($schema['offers']['priceCurrency'])->toBe('USD');
});

it('uses defaults when no product', function () {
    $meta = SeoService::metaTags(null, ['title' => 'Custom Title']);
    expect($meta['title'])->toBe('Custom Title');
});

it('returns null schema when no product', function () {
    expect(SeoService::schemaJsonLd(null))->toBeNull();
});
