@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

@extends($isAr ? 'layouts.app-rtl' : 'layouts.app')

@section('content')
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-accent/5 via-transparent to-transparent pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="tech-tag">{{ __($product->type) }}</span>
                            <span class="px-2.5 py-0.5 text-[10px] text-success bg-success/10 border border-success/20 rounded-full">{{ __('Instant Download') }}</span>
                            @if($product->schema_type === 'SoftwareApplication')
                                <span class="px-2.5 py-0.5 text-[10px] text-violet-accent bg-violet-accent/10 border border-violet-accent/20 rounded-full">{{ __('Multi-Tenant') }}</span>
                            @endif
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4">{{ $isAr ? $product->name_ar : $product->name_en }}</h1>
                        <p class="text-muted-400 leading-relaxed text-lg">{{ $isAr ? $product->description_ar : $product->description_en }}</p>
                    </div>

                    @php $features = $isAr ? $product->features_ar : $product->features_en; @endphp
                    @if($features && is_array($features))
                        <div class="glass-card p-6">
                            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                {{ __('Features') }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($features as $feature)
                                    <div class="flex items-start gap-3 p-3 rounded-lg bg-white/5 border border-slate-700/20">
                                        <svg class="w-4 h-4 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm text-muted-400">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="glass-card p-6 overflow-hidden">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            {{ __('Technical Specifications') }}
                        </h2>
                        <div class="bg-deep-950/80 rounded-lg p-4 border border-slate-700/30 font-mono text-sm" style="direction: ltr;">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-success/80"></span>
                                <span class="text-muted-400 text-xs ml-2">terminal ~/riof</span>
                            </div>
                            <div class="space-y-1.5">
                                <p><span class="text-success">$</span> <span class="text-accent-light">product</span> info --slug={{ $product->slug }}</p>
                                <p><span class="text-muted-400">├── name:</span> {{ $product->name_en }}</p>
                                <p><span class="text-muted-400">├── type:</span> {{ $product->type }}</p>
                                <p><span class="text-muted-400">├── price_usd:</span> ${{ number_format($product->price_usd, 2) }}</p>
                                <p><span class="text-muted-400">├── price_sar:</span> {{ number_format($product->price_sar, 2) }} SAR</p>
                                <p><span class="text-muted-400">├── schema:</span> <span class="text-violet-accent">{{ $product->schema_type }}</span></p>
                                <p><span class="text-muted-400">└── status:</span> <span class="text-success">active</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="glass-card p-6 sticky top-24 space-y-4">
                        <div>
                            <div class="text-3xl font-extrabold text-accent-light">{{ format_price($product->price_usd) }}</div>
                            <p class="text-sm text-muted-400 mt-1">{{ __('Price') }} (SAR): <span class="text-white">{{ format_price($product->price_usd, 'sar') }}</span></p>
                        </div>

                        <div class="space-y-2 text-xs text-muted-400">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('Digital License - Instant Access') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                {{ __('Secure Download') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                                {{ __('Lifetime Updates') }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('cart.add') }}" id="add-to-cart-form" data-ajax-add="1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="w-full py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                {{ __('Add to Cart' )}}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($related->isNotEmpty())
                <div class="mt-16">
                    <h2 class="text-2xl font-bold text-white mb-6">{{ __('Related Products') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($related as $rel)
                            <a href="{{ route('products.show', $rel->slug) }}" class="glass-card p-5 hover:glow-border transition-all duration-300 group">
                                <h3 class="font-semibold text-white group-hover:text-accent-light transition-colors">{{ $isAr ? $rel->name_ar : $rel->name_en }}</h3>
                                <p class="text-accent-light font-bold mt-2">{{ format_price($rel->price_usd) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('add-to-cart-form');
            if (!form || !form.dataset.ajaxAdd) return;

            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (btn.disabled) return;
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> {{ __('Adding...') }}';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        const rect = btn.getBoundingClientRect();
                        if (window.riofFlyToCart) window.riofFlyToCart(rect);
                        if (window.riofUpdateCartCount) window.riofUpdateCartCount(data.count);
                    }
                })
                .catch(function () {})
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            });
        });
    </script>
@endsection
