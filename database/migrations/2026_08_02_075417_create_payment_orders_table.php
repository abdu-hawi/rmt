<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->index(); // رقم الطلب الخاص بنظامك
            $table->string('trans_id')->nullable()->unique()->index(); // رقم العملية لدى بوابة الدفع
            $table->string('rrn')->nullable(); // رقم المرجع للبنك
            $table->string('action')->nullable(); // نوع الإجراء SALE, CAPTURE ...
            $table->string('result')->nullable(); // نتيجة البوابة SUCCESS, DECLINED ...
            $table->string('status')->default('pending'); // حالة العملية داخل تطبيقك
            $table->decimal('amount', 10, 2); // المبلغ
            $table->string('card_brand')->nullable(); // MADA, VISA ...
            $table->json('payload')->nullable(); // لحفظ كائن البيانات الكامل القادم من الـ Callback
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
