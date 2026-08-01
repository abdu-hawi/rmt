@extends('admin.layouts.admin')

@section('title', __('Order') . ' #' . $order->order_number)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-muted-400 hover:text-accent-light transition-colors">&larr; {{ __('Back to Orders') }}</a>
        <h1 class="text-2xl font-bold text-white mt-2">{{ __('Order') }} <span class="text-accent-light font-mono">#{{ $order->order_number }}</span></h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-5">
            <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ __('Payer Information') }}
            </h2>
            <div class="space-y-2 text-sm">
                @foreach(['payer_first_name' => __('First Name'), 'payer_last_name' => __('Last Name'), 'payer_email' => __('Email'), 'payer_phone' => __('Phone'), 'payer_address' => __('Address'), 'payer_city' => __('City'), 'payer_country' => __('Country'), 'payer_zip' => __('ZIP Code')] as $field => $label)
                    <div class="flex justify-between">
                        <span class="text-muted-400">{{ $label }}:</span>
                        <span class="text-white">{{ $order->{$field} }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="glass-card p-5">
            <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                {{ __('Order Details') }}
            </h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-muted-400">{{ __('Status') }}:</span>
                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full {{ $order->status === 'completed' ? 'bg-success/10 text-success border border-success/20' : ($order->status === 'cancelled' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20') }}">
                        {{ __(ucfirst($order->status)) }}
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-muted-400">{{ __('Currency') }}:</span><span class="text-white font-mono">{{ strtoupper($order->currency) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-400">{{ __('Subtotal (Excl. VAT)') }}:</span><span class="text-white">{{ \App\Services\CurrencyService::format($order->subtotal, $order->currency) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-400">{{ __('Discount') }}:</span><span class="text-success">-{{ \App\Services\CurrencyService::format($order->discount, $order->currency) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-400">{{ __('VAT (:rate%)', ['rate' => config('tax.vat_rate_percent')]) }}:</span><span class="text-white">{{ \App\Services\CurrencyService::format($order->vat, $order->currency) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-400 font-medium">{{ __('Grand Total') }}:</span><span class="text-accent-light font-bold">{{ \App\Services\CurrencyService::format($order->total, $order->currency) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-400">{{ __('Date') }}:</span><span class="text-white">{{ $order->created_at->format('Y-m-d H:i') }}</span></div>
            </div>

            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-5 flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="input-floating flex-1">
                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
                <button class="px-4 py-2 bg-accent hover:bg-accent/90 text-white text-sm font-semibold rounded-lg transition-all">{{ __('Update') }}</button>
            </form>
        </div>
    </div>

    <div class="glass-card p-5 mt-6">
        <h2 class="font-semibold text-white mb-4">{{ __('Order Items') }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-700/30"><th class="text-left p-3 text-muted-400 font-medium">{{ __('Product') }}</th><th class="text-left p-3 text-muted-400 font-medium">{{ __('Price') }}</th><th class="text-left p-3 text-muted-400 font-medium">{{ __('Quantity') }}</th><th class="text-left p-3 text-muted-400 font-medium">{{ __('Subtotal') }}</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="border-b border-slate-700/20">
                            <td class="p-3 text-white">{{ $item->product_name }}</td>
                            <td class="p-3 text-muted-400 font-mono">{{ \App\Services\CurrencyService::format($item->price, $order->currency) }}</td>
                            <td class="p-3 text-white">{{ $item->quantity }}</td>
                            <td class="p-3 text-accent-light font-mono">{{ \App\Services\CurrencyService::format($item->price * $item->quantity, $order->currency) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
