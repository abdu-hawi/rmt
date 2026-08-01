@php
    $seo = \App\Services\SeoService::metaTags(null, ['title' => __('Checkout') . ' - ' . config('seo.site_name')]);
@endphp

@extends(is_rtl() ? 'layouts.app-rtl' : 'layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16">
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white">{{ __('Checkout') }}</h1>
            <p class="mt-2 text-muted-400">{{ __('Complete your purchase in seconds') }}</p>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-5 gap-8 max-w-5xl mx-auto">
            @csrf

            <div class="lg:col-span-3">
                <div class="glass-card p-8 space-y-6">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('Payer Information') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="relative">
                            <input type="text" name="payer_first_name" value="{{ old('payer_first_name') }}" required placeholder=" " maxlength="100"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('First Name') }} *</label>
                            @error('payer_first_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="relative">
                            <input type="text" name="payer_last_name" value="{{ old('payer_last_name') }}" required placeholder=" " maxlength="100"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Last Name') }} *</label>
                            @error('payer_last_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2 relative">
                            <input type="text" name="payer_address" value="{{ old('payer_address') }}" required placeholder=" " maxlength="500"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Address') }} *</label>
                            @error('payer_address')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="relative">
                            <input type="text" name="payer_country" value="{{ old('payer_country') }}" required placeholder=" " maxlength="100"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Country') }} *</label>
                            @error('payer_country')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="relative">
                            <input type="text" name="payer_city" value="{{ old('payer_city') }}" required placeholder=" " maxlength="100"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('City') }} *</label>
                            @error('payer_city')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="relative">
                            <input type="email" name="payer_email" value="{{ old('payer_email') }}" required placeholder=" " maxlength="255"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Email') }} *</label>
                            @error('payer_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="relative">
                            <input type="text" name="payer_phone" value="{{ old('payer_phone') }}" required placeholder=" " maxlength="50"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Phone') }} *</label>
                            @error('payer_phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="relative">
                            <input type="text" name="payer_zip" value="{{ old('payer_zip') }}" required placeholder=" " maxlength="20"
                                   class="peer input-floating pt-5 pb-2">
                            <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('ZIP Code') }} *</label>
                            @error('payer_zip')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="glass-card p-6 space-y-4 sticky top-24">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ __('Order Summary') }}
                    </h2>

                    <div class="space-y-3">
                        @foreach($items as $item)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-slate-700/20">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ $item['name'] }}</p>
                                    <p class="text-xs text-muted-400">{{ __('Qty') }}: {{ $item['quantity'] }}</p>
                                </div>
                                <span class="text-sm font-semibold text-accent-light flex-shrink-0 ml-3">{{ \App\Services\CurrencyService::format($item['price_usd'] * $item['quantity']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 pt-3 border-t border-slate-700/30 text-sm">
                        <div class="flex justify-between"><span class="text-muted-400">{{ __('Subtotal (Excl. VAT)') }}</span><span class="text-white">{{ \App\Services\CurrencyService::format($subtotal) }}</span></div>
                        @if($discount > 0)
                            <div class="flex justify-between"><span class="text-muted-400">{{ __('Discount') }}</span><span class="text-success">-{{ \App\Services\CurrencyService::format($discount) }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-muted-400">{{ __('VAT (:rate%)', ['rate' => config('tax.vat_rate_percent')]) }}</span><span class="text-white">{{ \App\Services\CurrencyService::format($vat) }}</span></div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-700/30">
                            <span class="text-white">{{ __('Grand Total') }}</span>
                            <span class="text-accent-light">{{ \App\Services\CurrencyService::format($total) }}</span>
                        </div>
                    </div>

                    <button class="w-full py-3.5 bg-accent hover:bg-accent/90 text-white font-bold rounded-lg transition-all glow-border flex items-center justify-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Place Order') }}
                    </button>

                    <p class="text-[11px] text-muted-400 text-center">{{ __('Your purchase is secure and encrypted') }}</p>
                </div>
            </div>
        </form>
    </div>
@endsection
