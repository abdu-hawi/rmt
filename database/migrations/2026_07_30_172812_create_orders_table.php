<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payer_first_name');
            $table->string('payer_last_name');
            $table->string('payer_address');
            $table->string('payer_country');
            $table->string('payer_city');
            $table->string('payer_email');
            $table->string('payer_phone');
            $table->string('payer_zip');
            $table->enum('currency', ['usd', 'sar'])->default('usd');
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
