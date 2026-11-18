<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id',
        'product_name', 'price', 'quantity',
        'is_tax_inclusive', 'net_total', 'tax_amount',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
            'net_total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    /**
     * Line total including VAT, built from the stored snapshot so the value
     * never changes when the product is edited later.
     */
    public function getGrossTotalAttribute(): float
    {
        return round((float) $this->net_total + (float) $this->tax_amount, 2);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
