<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('type')->default('digital');
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en');
            $table->text('description_ar');
            $table->decimal('price_usd', 14, 2);
            $table->decimal('price_sar', 14, 2);
            $table->json('features_en')->nullable();
            $table->json('features_ar')->nullable();
            $table->string('seo_title_en')->nullable();
            $table->string('seo_title_ar')->nullable();
            $table->text('seo_description_en')->nullable();
            $table->text('seo_description_ar')->nullable();
            $table->text('seo_keywords_en')->nullable();
            $table->text('seo_keywords_ar')->nullable();
            $table->string('schema_type')->default('SoftwareApplication');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('download_url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
