@php $isAr = app()->getLocale() === 'ar'; @endphp

@extends($isAr ? 'layouts.app-rtl' : 'layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 pt-12 pb-16">
        <div class="glass-card p-8 text-center space-y-6">
            <div class="w-16 h-16 mx-auto bg-success/10 border-2 border-success/30 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white">{{ __('Order Confirmation') }}</h1>
                <p class="text-muted-400 mt-2">{{ __('Thank you for your purchase') }}</p>
            </div>

            <div class="glass p-4 rounded-lg space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-muted-400">{{ __('Order Number') }}</span>
                    <span class="text-white font-mono font-bold text-accent-light">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted-400">{{ __('Status') }}</span>
                    <span class="px-2.5 py-0.5 text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 rounded-full">{{ __(ucfirst($order->status)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted-400">{{ __('Subtotal (Excl. VAT)') }}</span>
                    <span class="text-white">{{ \App\Services\CurrencyService::format($order->subtotal, $order->currency) }}</span>
                </div>
                @if((float) $order->discount > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-muted-400">{{ __('Discount') }}</span>
                        <span class="text-success">-{{ \App\Services\CurrencyService::format($order->discount, $order->currency) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-muted-400">{{ __('VAT (:rate%)', ['rate' => config('tax.vat_rate_percent')]) }}</span>
                    <span class="text-white">{{ \App\Services\CurrencyService::format($order->vat, $order->currency) }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-700/30">
                    <span class="text-muted-400 font-medium">{{ __('Grand Total') }}</span>
                    <span class="text-white font-bold">{{ \App\Services\CurrencyService::format($order->total, $order->currency) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted-400">{{ __('Email') }}</span>
                    <span class="text-white">{{ $order->payer_email }}</span>
                </div>
            </div>

            <div>
                <h2 class="font-semibold text-white text-left mb-3">{{ __('Order Items') }}</h2>
                <div class="space-y-2">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center p-3 rounded-lg bg-white/5 border border-slate-700/20 text-sm">
                            <span class="text-white">{{ $item->product_name }}</span>
                            <span class="text-accent-light font-semibold">{{ \App\Services\CurrencyService::format($item->price, $order->currency) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                {{ __('Continue Shopping') }}
            </a>
        </div>
    </div>
@endsection
