@extends('admin.layouts.admin')

@section('title', __('Customers Management') . ' - Riof Admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">{{ __('Customers Management') }}</h1>
        <p class="text-muted-400 text-sm mt-1">{{ __('View and manage registered customers') }}</p>
    </div>

    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name or email') }}..." class="input-floating flex-1">
        <button class="px-5 py-2.5 bg-white/10 hover:bg-white/15 text-white text-sm font-medium rounded-lg transition-all">{{ __('Search') }}</button>
    </form>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/30 bg-white/5">
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Name') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Email') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Registered') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                        <tr class="border-b border-slate-700/20 hover:bg-white/5 transition-all">
                            <td class="p-4 text-white">{{ $customer->name }}</td>
                            <td class="p-4 text-muted-400">{{ $customer->email }}</td>
                            <td class="p-4 text-muted-400 text-xs">{{ $customer->created_at->format('Y-m-d') }}</td>
                            <td class="p-4"><a href="{{ route('admin.customers.show', $customer) }}" class="px-3 py-1.5 text-xs font-medium text-accent-light bg-accent/10 hover:bg-accent/20 rounded-lg transition-all">{{ __('View') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
