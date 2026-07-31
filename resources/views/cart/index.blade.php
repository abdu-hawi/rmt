@php
    $seo = \App\Services\SeoService::metaTags(null, ['title' => __('Cart') . ' - ' . config('seo.site_name')]);
@endphp

@extends(is_rtl() ? 'layouts.app-rtl' : 'layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-8">{{ __('Cart') }}</h1>

        @if($items->isEmpty())
            <div class="text-center py-20">
                <svg class="w-20 h-20 mx-auto text-muted-400/20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <p class="text-xl text-muted-400 mb-6">{{ __('Your cart is empty') }}</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    {{ __('Continue Shopping') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    @foreach($items as $item)
                        <div class="glass-card p-5 flex items-center justify-between group">
                            <div class="flex-1">
                                <h3 class="font-semibold text-white">{{ $item['name'] }}</h3>
                                <p class="text-sm text-muted-400 mt-1">{{ format_price($item['price_usd']) }} &times; {{ $item['quantity'] }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="100"
                                           class="w-16 bg-carbon-900/80 border border-slate-700/50 rounded-lg px-2 py-1.5 text-sm text-white text-center focus:outline-none focus:border-accent/60">
                                    <button class="px-2.5 py-1.5 bg-accent/10 text-accent-light text-xs font-medium rounded-lg hover:bg-accent/20 transition-all">{{ __('Update') }}</button>
                                </form>
                                <form method="POST" action="{{ route('cart.remove', $item['id']) }}">
                                    @csrf
                                    <button class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div class="glass-card p-6 space-y-4">
                        <form method="POST" action="{{ route('cart.coupon') }}">
                            @csrf
                            <label class="block text-sm font-medium text-muted-400 mb-2">{{ __('Apply Coupon') }}</label>
                            <div class="flex gap-2">
                                <input type="text" name="code" placeholder="{{ __('Enter coupon code') }}" class="input-floating text-sm flex-1">
                                <button class="px-4 py-2 bg-white/10 hover:bg-white/15 text-white text-sm font-medium rounded-lg transition-all">{{ __('Apply') }}</button>
                            </div>
                        </form>

                        @if($coupon)
                            <div class="p-3 bg-success/10 border border-success/20 rounded-lg text-sm flex justify-between items-center">
                                <span class="text-success font-mono">{{ $coupon->code }}</span>
                                <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                    @csrf
                                    <button class="text-red-400 hover:text-red-300 text-xs">{{ __('Remove') }}</button>
                                </form>
                            </div>
                        @endif

                        <div class="space-y-3 pt-3 border-t border-slate-700/30">
                            <div class="flex justify-between text-sm"><span class="text-muted-400">{{ __('Subtotal') }}</span><span class="text-white">{{ \App\Services\CurrencyService::format($subtotal) }}</span></div>
                            @if($discount > 0)
                                <div class="flex justify-between text-sm"><span class="text-muted-400">{{ __('Discount') }}</span><span class="text-success">-{{ \App\Services\CurrencyService::format($discount) }}</span></div>
                            @endif
                            <div class="flex justify-between text-lg font-bold border-t border-slate-700/30 pt-3"><span class="text-white">{{ __('Total') }}</span><span class="text-accent-light">{{ \App\Services\CurrencyService::format($total) }}</span></div>
                        </div>

                        <a href="{{ route('checkout.index') }}" class="block w-full py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg text-center transition-all glow-border">
                            {{ __('Proceed to Checkout') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
