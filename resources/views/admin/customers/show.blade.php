@extends('admin.layouts.admin')

@section('title', $customer->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.customers.index') }}" class="text-sm text-muted-400 hover:text-accent-light transition-colors">&larr; {{ __('Back to Customers') }}</a>
        <h1 class="text-2xl font-bold text-white mt-2">{{ $customer->name }}</h1>
    </div>

    <div class="glass-card p-5 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center">
                <span class="text-lg font-bold text-accent-light">{{ substr($customer->name, 0, 1) }}</span>
            </div>
            <div>
                <p class="text-white font-semibold">{{ $customer->name }}</p>
                <p class="text-sm text-muted-400">{{ $customer->email }}</p>
                <p class="text-xs text-muted-400 mt-1">{{ __('Registered') }}: {{ $customer->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>

    <h2 class="text-lg font-bold text-white mb-4">{{ __('Orders') }}</h2>
    @if($orders->isEmpty())
        <div class="glass-card p-8 text-center">
            <p class="text-muted-400">{{ __('No orders yet') }}</p>
        </div>
    @else
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-slate-700/30 bg-white/5"><th class="text-left p-4 text-muted-400 font-medium">{{ __('Order') }}</th><th class="text-left p-4 text-muted-400 font-medium">{{ __('Total') }}</th><th class="text-left p-4 text-muted-400 font-medium">{{ __('Status') }}</th><th class="text-left p-4 text-muted-400 font-medium">{{ __('Date') }}</th></tr></thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="border-b border-slate-700/20 hover:bg-white/5">
                                <td class="p-4"><a href="{{ route('admin.orders.show', $order) }}" class="text-accent-light font-mono text-xs">{{ $order->order_number }}</a></td>
                                <td class="p-4 text-muted-400 font-mono">{{ \App\Services\CurrencyService::format($order->total, $order->currency) }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $order->status === 'completed' ? 'bg-success/10 text-success border border-success/20' : ($order->status === 'cancelled' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20') }}">
                                        {{ __(ucfirst($order->status)) }}
                                    </span>
                                </td>
                                <td class="p-4 text-muted-400 text-xs">{{ $order->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
