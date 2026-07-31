@extends('admin.layouts.admin')

@section('title', __('Dashboard') . ' - Riof Admin')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">{{ __('Dashboard') }}</h1>
        <p class="text-muted-400 text-sm mt-1">{{ __('Overview of your digital store') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="glass-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-sm text-muted-400">{{ __('Products') }}</p>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['total_products'] }}</p>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="text-sm text-muted-400">{{ __('Orders') }}</p>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-muted-400">{{ __('Revenue') }}</p>
            </div>
            <p class="text-3xl font-bold text-success">${{ number_format($stats['total_revenue'], 2) }}</p>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-violet-accent/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                </div>
                <p class="text-sm text-muted-400">{{ __('Customers') }}</p>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['total_customers'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-5">
            <h2 class="font-semibold text-white mb-4">{{ __('Orders by Status') }}</h2>
            <div class="space-y-3">
                @foreach($stats['orders_by_status'] as $status => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $status === 'completed' ? 'bg-success' : ($status === 'cancelled' ? 'bg-red-500' : 'bg-yellow-500') }}"></span>
                            <span class="text-sm text-muted-400">{{ __(ucfirst($status)) }}</span>
                        </div>
                        <span class="text-sm font-bold text-white">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="glass-card p-5">
            <h2 class="font-semibold text-white mb-4">{{ __('Recent Orders') }}</h2>
            @if($stats['recent_orders']->isEmpty())
                <p class="text-sm text-muted-400">{{ __('No orders yet') }}</p>
            @else
                <div class="space-y-2">
                    @foreach($stats['recent_orders'] as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-all text-sm">
                            <span class="text-white font-mono text-xs">{{ $order->order_number }}</span>
                            <span class="text-accent-light font-semibold">${{ number_format($order->total, 2) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
