<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? config('seo.default_title') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? config('seo.default_description') }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? config('seo.default_keywords') }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if(isset($seo['og']))
    <meta property="og:title" content="{{ $seo['og']['title'] }}">
    <meta property="og:description" content="{{ $seo['og']['description'] }}">
    <meta property="og:type" content="{{ $seo['og']['type'] }}">
    <meta property="og:url" content="{{ $seo['og']['url'] }}">
    <meta property="og:image" content="{{ $seo['og']['image'] }}">
    <meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
    <meta property="og:locale" content="{{ $seo['og']['locale'] }}">
    @endif

    @if(isset($seo['twitter']))
    <meta name="twitter:card" content="{{ $seo['twitter']['card'] }}">
    <meta name="twitter:site" content="{{ $seo['twitter']['site'] }}">
    <meta name="twitter:title" content="{{ $seo['twitter']['title'] }}">
    <meta name="twitter:description" content="{{ $seo['twitter']['description'] }}">
    @endif

    @if(isset($schema))
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    @vite(['resources/css/app.css'])
</head>
<body>
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-slate-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-xl font-extrabold bg-gradient-to-r from-accent to-accent-light bg-clip-text text-transparent">Riof</a>
                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('products.index') }}" class="px-3 py-2 text-sm text-muted-400 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-200">{{ __('Products') }}</a>
                        <a href="{{ route('cart.index') }}" class="relative px-3 py-2 text-sm text-muted-400 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-200">
                            {{ __('Cart') }}
                            @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                            @if($cartCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-5 h-5 flex items-center justify-center bg-accent text-white text-[10px] font-bold rounded-full">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 bg-carbon-900/60 rounded-lg p-1 border border-slate-700/30">
                        <a href="{{ route('lang.switch', 'en') }}" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}">EN</a>
                        <a href="{{ route('lang.switch', 'ar') }}" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'ar' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}" style="font-family: 'Cairo', sans-serif;">AR</a>
                    </div>

                    <div class="flex items-center gap-1.5 bg-carbon-900/60 rounded-lg p-1 border border-slate-700/30">
                        <a href="{{ route('currency.switch', 'usd') }}" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ app(\App\Services\CurrencyService::class)::current() === 'usd' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}">{{ __('USD') }}</a>
                        <a href="{{ route('currency.switch', 'sar') }}" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ app(\App\Services\CurrencyService::class)::current() === 'sar' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}">{{ __('SAR') }}</a>
                    </div>

                    <div class="h-6 w-px bg-slate-700/50"></div>

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 text-xs font-medium text-muted-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-all">{{ __('Admin') }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button class="px-3 py-1.5 text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-all">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-1.5 text-xs font-semibold text-white bg-accent hover:bg-accent/90 rounded-lg transition-all glow-border">{{ __('Login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-16 min-h-screen">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
                <div class="p-3 bg-success/10 border border-success/20 text-success rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
                <div class="p-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-slate-800/50 bg-deep-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 text-center">
            <p class="text-sm text-muted-400">&copy; {{ date('Y') }} <span class="text-white font-semibold">Riof</span>. {{ __('All rights reserved') }}.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-flash]').forEach(el => {
                setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity 0.5s'; }, 4000);
            });
        });
    </script>
</body>
</html>
