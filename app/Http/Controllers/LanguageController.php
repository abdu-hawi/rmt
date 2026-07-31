<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Localize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        if (in_array($locale, Localize::SUPPORTED)) {
            Session::put(Localize::LOCALE_KEY, $locale);
        }

        return redirect()->back();
    }

    public function currency($currency)
    {
        if (in_array($currency, ['usd', 'sar'])) {
            Session::put('currency', $currency);
        }

        return redirect()->back();
    }
}
