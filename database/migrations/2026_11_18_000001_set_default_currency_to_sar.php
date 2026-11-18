<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'default_currency')
            ->update(['value' => 'sar']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'default_currency')
            ->update(['value' => 'usd']);
    }
};
