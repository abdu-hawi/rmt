<?php

return [
    'default' => env('CURRENCY_DEFAULT', 'sar'),
    'rate_usd_to_sar' => env('CURRENCY_USD_TO_SAR', 3.75),
    'symbols' => [
        'usd' => '$',
        'sar' => 'ر.س',
    ],
    'names' => [
        'usd' => 'USD',
        'sar' => 'SAR',
    ],
];
