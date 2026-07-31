<?php

namespace App\Services;

use App\Models\Product;

class SeoService
{
    public static function metaTags(Product $product = null, array $overrides = []): array
    {
        $locale = app()->getLocale();
        $isAr = $locale === 'ar';
        $url = url()->current();
        $title = $overrides['title'] ?? ($product
            ? ($isAr ? $product->seo_title_ar : $product->seo_title_en)
            : config('seo.default_title'));
        $description = $overrides['description'] ?? ($product
            ? ($isAr ? $product->seo_description_ar : $product->seo_description_en)
            : config('seo.default_description'));
        $keywords = $overrides['keywords'] ?? ($product
            ? ($isAr ? $product->seo_keywords_ar : $product->seo_keywords_en)
            : config('seo.default_keywords'));
        $image = $overrides['image'] ?? '';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $url,
            'og' => [
                'title' => $title,
                'description' => $description,
                'type' => 'website',
                'url' => $url,
                'image' => $image,
                'site_name' => config('seo.site_name'),
                'locale' => $locale === 'ar' ? 'ar_SA' : 'en_US',
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'site' => config('seo.twitter_handle'),
                'title' => $title,
                'description' => $description,
                'image' => $image,
            ],
        ];
    }

    public static function schemaJsonLd(Product $product = null): ?array
    {
        if (!$product) {
            return null;
        }

        $locale = app()->getLocale();
        $isAr = $locale === 'ar';

        return [
            '@context' => 'https://schema.org',
            '@type' => $product->schema_type ?: 'SoftwareApplication',
            'name' => $isAr ? $product->name_ar : $product->name_en,
            'description' => $isAr ? $product->description_ar : $product->description_en,
            'applicationCategory' => 'BusinessApplication',
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $product->price_usd,
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
            ],
            'url' => route('products.show', $product->slug),
        ];
    }
}
