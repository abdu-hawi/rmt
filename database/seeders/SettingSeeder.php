<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create(['key' => 'store_name', 'value' => 'Riof Digital Store']);
        Setting::create(['key' => 'store_description', 'value' => 'Premium digital products and enterprise software solutions.']);
        Setting::create(['key' => 'default_currency', 'value' => 'usd']);
        Setting::create(['key' => 'default_language', 'value' => 'en']);
        Setting::create(['key' => 'exchange_rate_usd_to_sar', 'value' => '3.75']);
        Setting::create(['key' => 'admin_email', 'value' => 'admin@riofdigital.com']);
    }
}
