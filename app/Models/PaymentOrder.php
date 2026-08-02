<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
