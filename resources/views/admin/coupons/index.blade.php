@extends('admin.layouts.admin')

@section('title', __('Coupons Management') . ' - Riof Admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ __('Coupons Management') }}</h1>
            <p class="text-muted-400 text-sm mt-1">{{ __('Create and manage discount coupons') }}</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-accent hover:bg-accent/90 text-white text-sm font-semibold rounded-lg transition-all glow-border flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Create') }}
        </a>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/30 bg-white/5">
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Code') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Type') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Value') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Uses') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Expires') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Status') }}</th>
                        <th class="text-left p-4 text-muted-400 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $coupon)
                        <tr class="border-b border-slate-700/20 hover:bg-white/5 transition-all">
                            <td class="p-4 text-white font-mono">{{ $coupon->code }}</td>
                            <td class="p-4"><span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $coupon->type === 'percentage' ? 'bg-violet-accent/10 text-violet-accent border border-violet-accent/20' : 'bg-accent/10 text-accent-light border border-accent/20' }}">{{ __(ucfirst($coupon->type)) }}</span></td>
                            <td class="p-4 text-white font-mono">{{ $coupon->type === 'percentage' ? $coupon->value . '%' : '$' . $coupon->value }}</td>
                            <td class="p-4 text-muted-400">{{ $coupon->used_count }}{{ $coupon->max_uses ? '/' . $coupon->max_uses : '' }}</td>
                            <td class="p-4 text-muted-400 text-xs">{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '-' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $coupon->is_active ? 'bg-success/10 text-success border border-success/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    {{ $coupon->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="px-3 py-1.5 text-xs font-medium text-accent-light bg-accent/10 hover:bg-accent/20 rounded-lg transition-all">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-all">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
@endsection
