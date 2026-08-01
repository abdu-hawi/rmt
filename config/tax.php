<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ZATCA VAT Configuration
    |--------------------------------------------------------------------------
    |
    | All product base prices are stored exclusive of VAT.
    | VAT is applied AFTER coupon discounts on the taxable subtotal.
    |
    */

    'vat_rate' => env('VAT_RATE', 0.15),

    'vat_rate_percent' => (int) round(env('VAT_RATE', 0.15) * 100),

    'shipping_taxable' => env('SHIPPING_TAXABLE', false),

];
