<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'slug', 'type', 'category_id',
        'name_en', 'name_ar',
        'description_en', 'description_ar',
        'price_usd', 'price_sar',
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
            'price_usd' => 'decimal:2',
            'price_sar' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
