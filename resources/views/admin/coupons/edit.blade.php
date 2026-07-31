@extends('admin.layouts.admin')

@section('title', __('Edit Coupon') . ' - ' . $coupon->code)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.coupons.index') }}" class="text-sm text-muted-400 hover:text-accent-light transition-colors">&larr; {{ __('Back') }}</a>
        <h1 class="text-2xl font-bold text-white mt-2">{{ __('Edit Coupon') }}: <span class="text-accent-light font-mono">{{ $coupon->code }}</span></h1>
    </div>

    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="glass-card p-6 max-w-lg space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Coupon Code') }} *</label>
            <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required class="input-floating font-mono">
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Type') }} *</label>
                <select name="type" class="input-floating">
                    <option value="percentage" {{ $coupon->type === 'percentage' ? 'selected' : '' }}>{{ __('Percentage') }}</option>
                    <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>{{ __('Fixed') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Value') }} *</label>
                <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" required class="input-floating">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Min Order Amount') }}</label>
                <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="input-floating">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Max Uses') }}</label>
                <input type="number" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" class="input-floating">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Expires At') }}</label>
            <input type="date" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}" class="input-floating">
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $coupon->is_active ? 'checked' : '' }} class="rounded border-slate-700 bg-carbon-900 text-accent focus:ring-accent/30">
                <span class="text-white">{{ __('Active') }}</span>
            </label>
        </div>

        <button class="px-6 py-2.5 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border">{{ __('Save') }}</button>
    </form>
@endsection
