<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'slug', 'type', 'category_id',
        'name_en', 'name_ar',
        'description_en', 'description_ar',
        'price_usd', 'price_sar', 'is_tax_inclusive',
        'features_en', 'features_ar',
        'seo_title_en', 'seo_title_ar',
        'seo_description_en', 'seo_description_ar',
        'seo_keywords_en', 'seo_keywords_ar',
        'schema_type', 'is_active', 'sort_order', 'download_url',
    ];

    protected function casts(): array
    {
        return [
            'features_en' => 'array',
            'features_ar' => 'array',
            'is_active' => 'boolean',
            'is_tax_inclusive' => 'boolean',
            'price_usd' => 'decimal:2',
            'price_sar' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Price stored for the given currency.
     *
     * Both prices are entered by the admin and must be used verbatim: deriving
     * SAR from `price_usd × rate` introduced cent drift (200,000 → 199,999.99).
     */
    public function priceFor(string $currency): float
    {
        $currency = strtolower($currency);

        return (float) ($currency === CurrencyService::SAR ? $this->price_sar : $this->price_usd);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
