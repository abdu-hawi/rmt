<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? config('seo.default_title') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? config('seo.default_description') }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? config('seo.default_keywords') }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-mark.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#6366f1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap"
        rel="stylesheet">

    @if (isset($seo['og']))
        <meta property="og:title" content="{{ $seo['og']['title'] }}">
        <meta property="og:description" content="{{ $seo['og']['description'] }}">
        <meta property="og:type" content="{{ $seo['og']['type'] }}">
        <meta property="og:url" content="{{ $seo['og']['url'] }}">
        <meta property="og:image" content="{{ $seo['og']['image'] }}">
        <meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
        <meta property="og:locale" content="ar_SA">
    @endif

    @if (isset($seo['twitter']))
        <meta name="twitter:card" content="{{ $seo['twitter']['card'] }}">
        <meta name="twitter:site" content="{{ $seo['twitter']['site'] }}">
        <meta name="twitter:title" content="{{ $seo['twitter']['title'] }}">
        <meta name="twitter:description" content="{{ $seo['twitter']['description'] }}">
    @endif

    @if (isset($schema))
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    @vite(['resources/css/app.css'])
</head>

<body>
    @php
        $cartService = app(\App\Services\CartService::class);
        $cartCount = $cartService->count();
        $cartItems = $cartService->items();
        $cartSubtotal = $cartService->subtotal();
        $currentCurrency = \App\Services\CurrencyService::current();
    @endphp
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-slate-800/50"
        style="font-family: 'Cairo', 'Tajawal', sans-serif;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo-mark.svg') }}" alt="ريوف" class="w-8 h-8 rounded-lg">
                        <span
                            class="text-xl font-extrabold bg-gradient-to-r from-accent to-accent-light bg-clip-text text-transparent">ريوف</span>
                    </a>
                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('products.index') }}"
                            class="px-3 py-2 text-sm text-muted-400 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-200">{{ __('Products') }}</a>
                    </div>
                </div>

                <div class="flex items-center gap-2">

                    <div data-dropdown class="relative lg:hidden">
                        <button type="button" data-dropdown-toggle
                            class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-carbon-900/60 border border-slate-700/30 text-muted-400 hover:text-white hover:bg-white/5 transition-all"
                            aria-label="Language">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M2 12h20" />
                                <path
                                    d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" />
                            </svg>
                        </button>
                        <div data-dropdown-menu
                            class="hidden absolute end-0 top-11 w-44 bg-carbon-900/95 border border-slate-700/40 rounded-xl shadow-xl shadow-black/40 backdrop-blur-md overflow-hidden z-50">
                            <a href="{{ route('lang.switch', 'en') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-all {{ app()->getLocale() === 'en' ? 'text-white bg-white/5' : 'text-muted-400 hover:text-white hover:bg-white/5' }}">
                                <span
                                    class="w-2 h-2 rounded-full {{ app()->getLocale() === 'en' ? 'bg-accent' : 'bg-slate-600' }}"></span>
                                English
                            </a>
                            <a href="{{ route('lang.switch', 'ar') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-all {{ app()->getLocale() === 'ar' ? 'text-white bg-white/5' : 'text-muted-400 hover:text-white hover:bg-white/5' }}"
                                style="font-family: 'Cairo', sans-serif;">
                                <span
                                    class="w-2 h-2 rounded-full {{ app()->getLocale() === 'ar' ? 'bg-accent' : 'bg-slate-600' }}"></span>
                                العربية
                            </a>
                        </div>
                    </div>

                    <div
                        class="hidden lg:flex items-center gap-1.5 bg-carbon-900/60 rounded-lg p-1 border border-slate-700/30">
                        <a href="{{ route('lang.switch', 'en') }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}">EN</a>
                        <a href="{{ route('lang.switch', 'ar') }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'ar' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}"
                            style="font-family: 'Cairo', sans-serif;">AR</a>
                    </div>

                    <div data-dropdown class="relative lg:hidden">
                        <button type="button" data-dropdown-toggle
                            class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-carbon-900/60 border border-slate-700/30 text-muted-400 hover:text-white hover:bg-white/5 transition-all"
                            aria-label="Currency">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-currency-exchange" viewBox="0 0 16 16">
  <path d="M0 5a5 5 0 0 0 4.027 4.905 6.5 6.5 0 0 1 .544-2.073C3.695 7.536 3.132 6.864 3 5.91h-.5v-.426h.466V5.05q-.001-.07.004-.135H2.5v-.427h.511C3.236 3.24 4.213 2.5 5.681 2.5c.316 0 .59.031.819.085v.733a3.5 3.5 0 0 0-.815-.082c-.919 0-1.538.466-1.734 1.252h1.917v.427h-1.98q-.004.07-.003.147v.422h1.983v.427H3.93c.118.602.468 1.03 1.005 1.229a6.5 6.5 0 0 1 4.97-3.113A5.002 5.002 0 0 0 0 5m16 5.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0m-7.75 1.322c.069.835.746 1.485 1.964 1.562V14h.54v-.62c1.259-.086 1.996-.74 1.996-1.69 0-.865-.563-1.31-1.57-1.54l-.426-.1V8.374c.54.06.884.347.966.745h.948c-.07-.804-.779-1.433-1.914-1.502V7h-.54v.629c-1.076.103-1.808.732-1.808 1.622 0 .787.544 1.288 1.45 1.493l.358.085v1.78c-.554-.08-.92-.376-1.003-.787zm1.96-1.895c-.532-.12-.82-.364-.82-.732 0-.41.311-.719.824-.809v1.54h-.005zm.622 1.044c.645.145.943.38.943.796 0 .474-.37.8-1.02.86v-1.674z"/>
</svg>
                        </button>
                        <div data-dropdown-menu
                            class="hidden absolute end-0 top-11 w-40 bg-carbon-900/95 border border-slate-700/40 rounded-xl shadow-xl shadow-black/40 backdrop-blur-md overflow-hidden z-50">
                            <a href="{{ route('currency.switch', 'usd') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-all {{ $currentCurrency === 'usd' ? 'text-white bg-white/5' : 'text-muted-400 hover:text-white hover:bg-white/5' }}">
                                <span
                                    class="w-2 h-2 rounded-full {{ $currentCurrency === 'usd' ? 'bg-accent' : 'bg-slate-600' }}"></span>
                                {{ __('USD') }}
                            </a>
                            <a href="{{ route('currency.switch', 'sar') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-all {{ $currentCurrency === 'sar' ? 'text-white bg-white/5' : 'text-muted-400 hover:text-white hover:bg-white/5' }}">
                                <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 1124.14 1256.39" fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M699.62,1113.02h0c-20.06,44.48-33.32,92.75-38.4,143.37l424.51-90.24c20.06-44.47,33.31-92.75,38.4-143.37l-424.51,90.24Z" />
                                    <path
                                        d="M1085.73,895.8c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.33v-135.2l292.27-62.11c20.06-44.47,33.32-92.75,38.4-143.37l-330.68,70.27V66.13c-50.67,28.45-95.67,66.32-132.25,110.99v403.35l-132.25,28.11V0c-50.67,28.44-95.67,66.32-132.25,110.99v525.69l-295.91,62.88c-20.06,44.47-33.33,92.75-38.42,143.37l334.33-71.05v170.26l-358.3,76.14c-20.06,44.47-33.32,92.75-38.4,143.37l375.04-79.7c30.53-6.35,56.77-24.4,73.83-49.24l68.78-101.97v-.02c7.14-10.55,11.3-23.27,11.3-36.97v-149.98l132.25-28.11v270.4l424.53-90.28Z" />
                                </svg>
                                <span
                                    class="w-2 h-2 rounded-full flex-shrink-0 {{ $currentCurrency === 'sar' ? 'bg-accent' : 'bg-slate-600' }}"></span>
                                {{ __('SAR') }}
                            </a>
                        </div>
                    </div>

                    <div
                        class="hidden lg:flex items-center gap-1.5 bg-carbon-900/60 rounded-lg p-1 border border-slate-700/30">
                        <a href="{{ route('currency.switch', 'usd') }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ $currentCurrency === 'usd' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}">{{ __('USD') }}</a>
                        <a href="{{ route('currency.switch', 'sar') }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200 {{ $currentCurrency === 'sar' ? 'bg-accent text-white' : 'text-muted-400 hover:text-white' }}">{{ __('SAR') }}</a>
                    </div>

                    <div data-dropdown class="relative">
                        <button type="button" data-dropdown-toggle id="cart-btn"
                            class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-carbon-900/60 border border-slate-700/30 text-muted-400 hover:text-white hover:bg-white/5 transition-all"
                            aria-label="Cart">
                            <svg class="w-4 h-4 cart-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                            </svg>
                            @if ($cartCount > 0)
                                <span id="cart-count"
                                    class="absolute -top-1 -end-1 w-4 h-4 flex items-center justify-center bg-accent text-white text-[10px] font-bold rounded-full">{{ $cartCount }}</span>
                            @endif
                        </button>
                        <div data-dropdown-menu
                            class="hidden absolute end-0 top-11 w-72 bg-carbon-900/95 border border-slate-700/40 rounded-xl shadow-xl shadow-black/40 backdrop-blur-md overflow-hidden z-50">
                            <div class="px-4 py-2.5 border-b border-slate-700/30">
                                <p class="text-xs font-semibold text-white">{{ __('Cart') }} ({{ $cartCount }})
                                </p>
                            </div>
                            @if ($cartCount > 0)
                                <div class="max-h-56 overflow-y-auto">
                                    @foreach ($cartItems->take(3) as $item)
                                        <div class="flex items-center justify-between px-4 py-2 text-sm">
                                            <span
                                                class="text-white truncate flex-1 min-w-0">{{ $item['name'] }}</span>
                                            <span class="text-muted-400 text-xs ms-2 flex-shrink-0">&times;
                                                {{ $item['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                    @if ($cartItems->count() > 3)
                                        <div class="px-4 py-1.5 text-xs text-muted-400">+{{ $cartItems->count() - 3 }}
                                            {{ __('more') }}</div>
                                    @endif
                                </div>
                                <div
                                    class="flex items-center justify-between px-4 py-2.5 border-t border-slate-700/30 text-sm">
                                    <span class="text-muted-400">{{ __('Total') }}</span>
                                    <span
                                        class="text-white font-bold">{{ \App\Services\CurrencyService::format($cartSubtotal) }}</span>
                                </div>
                                <a href="{{ route('cart.index') }}"
                                    class="block px-4 py-2.5 text-center text-sm font-semibold text-white bg-accent hover:bg-accent/90 transition-all">{{ __('View Cart') }}</a>
                            @else
                                <div class="px-4 py-6 text-center text-sm text-muted-400">
                                    {{ __('Your cart is empty') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="h-6 w-px bg-slate-700/50"></div>

                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="px-3 py-1.5 text-xs font-medium text-muted-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-all">{{ __('Admin') }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                class="px-3 py-1.5 text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-all">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-4 py-1.5 text-xs font-semibold text-white bg-accent hover:bg-accent/90 rounded-lg transition-all glow-border">{{ __('Login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-16 min-h-screen">
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
                <div
                    class="p-3 bg-success/10 border border-success/20 text-success rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
                <div
                    class="p-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-slate-800/50 bg-deep-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 text-center">
            <p class="text-sm text-muted-400">&copy; {{ date('Y') }} <span
                    class="text-white font-semibold">ريوف</span>. {{ __('All rights reserved') }}.</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-flash]').forEach(el => {
                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity 0.5s';
                }, 4000);
            });

            // Nav dropdowns (language / currency / cart)
            function closeAllDropdowns(except) {
                document.querySelectorAll('[data-dropdown-menu]').forEach(menu => {
                    if (menu !== except) menu.classList.add('hidden');
                });
            }
            document.querySelectorAll('[data-dropdown]').forEach(dd => {
                const toggle = dd.querySelector('[data-dropdown-toggle]');
                const menu = dd.querySelector('[data-dropdown-menu]');
                if (!toggle || !menu) return;
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = !menu.classList.contains('hidden');
                    closeAllDropdowns();
                    if (!isOpen) menu.classList.remove('hidden');
                });
            });
            document.addEventListener('click', () => closeAllDropdowns());
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeAllDropdowns();
            });

            // Fly-to-Cart + badge bump (exposed globally for add-to-cart forms)
            window.riofFlyToCart = function(fromRect) {
                const btn = document.getElementById('cart-btn');
                if (!btn) return;
                const target = btn.getBoundingClientRect();
                const startX = fromRect.left + fromRect.width / 2;
                const startY = fromRect.top + fromRect.height / 2;
                const endX = target.left + target.width / 2;
                const endY = target.top + target.height / 2;

                const fly = document.createElement('div');
                fly.className = 'fly-to-cart-item';
                fly.textContent = '+1';
                fly.style.left = startX + 'px';
                fly.style.top = startY + 'px';
                document.body.appendChild(fly);

                const dx = endX - startX;
                const dy = endY - startY;

                requestAnimationFrame(() => {
                    fly.style.transition = 'transform 0.7s cubic-bezier(0.4, -0.3, 0.2, 1), opacity 0.7s ease';
                    fly.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(0.2)';
                    fly.style.opacity = '0.3';
                });

                setTimeout(() => {
                    fly.remove();
                    btn.classList.remove('cart-btn-bump');
                    void btn.offsetWidth;
                    btn.classList.add('cart-btn-bump');
                }, 700);
            };

            window.riofUpdateCartCount = function(count) {
                let badge = document.getElementById('cart-count');
                if (count > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.id = 'cart-count';
                        badge.className = 'absolute -top-1 -end-1 w-4 h-4 flex items-center justify-center bg-accent text-white text-[10px] font-bold rounded-full';
                        document.getElementById('cart-btn').appendChild(badge);
                    }
                    badge.textContent = count;
                    badge.classList.remove('cart-count-pop');
                    void badge.offsetWidth;
                    badge.classList.add('cart-count-pop');
                } else if (badge) {
                    badge.remove();
                }
            };
        });
    </script>
    @yield('scripts')
</body>

</html>
