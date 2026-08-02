<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'trans_id',
        'rrn',
        'action',
        'result',
        'status',
        'amount',
        'card_brand',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array', // تحويل تلقائي للـ JSON إلى Array عند الاستدعاء
    ];

    /**
     * العلاقة مع الطلب الداخلي (Order::id يُستخدم للعلاقات الداخلية و join)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
