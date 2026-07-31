@extends('admin.layouts.admin')

@section('title', __('Orders Management') . ' - Riof Admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">{{ __('Orders Management') }}</h1>
        <p class="text-muted-400 text-sm mt-1">{{ __('Track and manage customer orders') }}</p>
    </div>

    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by email or name') }}..." class="input-floating flex-1">
        <select name="status" class="input-floating w-40">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
        </select>
        <button class="px-5 py-2.5 bg-white/10 hover:bg-white/15 text-white text-sm font-medium rounded-lg transition-all">{{ __('Filter') }}</button>
    </form>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/30 bg-white/5">
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Order') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Customer') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Email') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Total') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Status') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Date') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr class="border-b border-slate-700/20 hover:bg-white/5 transition-all">
                            <td class="p-4 text-white font-mono text-xs">{{ $order->order_number }}</td>
                            <td class="p-4 text-white">{{ $order->payer_first_name }} {{ $order->payer_last_name }}</td>
                            <td class="p-4 text-muted-400">{{ $order->payer_email }}</td>
                            <td class="p-4 text-accent-light font-mono">{{ \App\Services\CurrencyService::format($order->total, $order->currency) }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $order->status === 'completed' ? 'bg-success/10 text-success border border-success/20' : ($order->status === 'cancelled' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20') }}">
                                    {{ __(ucfirst($order->status)) }}
                                </span>
                            </td>
                            <td class="p-4 text-muted-400 text-xs">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="p-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 text-xs font-medium text-accent-light bg-accent/10 hover:bg-accent/20 rounded-lg transition-all">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
