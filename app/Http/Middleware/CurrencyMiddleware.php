<?php

namespace App\Http\Middleware;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CurrencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $currency = $request->query('currency', Session::get('currency', config('currency.default')));

        if (in_array($currency, [CurrencyService::USD, CurrencyService::SAR])) {
            Session::put('currency', $currency);
        }

        view()->share('currentCurrency', CurrencyService::current());
        view()->share('currencySymbol', CurrencyService::symbol());

        return $next($request);
    }
}
