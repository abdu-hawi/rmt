<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localize
{
    public const LOCALE_KEY = 'locale';
    public const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get(self::LOCALE_KEY, $request->cookie(self::LOCALE_KEY, config('app.locale', 'en')));

        if (in_array($locale, self::SUPPORTED)) {
            App::setLocale($locale);
            Session::put(self::LOCALE_KEY, $locale);
        }

        return $next($request);
    }
}
