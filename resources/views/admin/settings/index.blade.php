@extends('admin.layouts.admin')

@section('title', __('Settings') . ' - Riof Admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">{{ __('Settings') }}</h1>
        <p class="text-muted-400 text-sm mt-1">{{ __('Configure your store preferences') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="glass-card p-6 max-w-lg space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Store Name') }}</label>
            <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? '') }}" class="input-floating">
        </div>

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Store Description') }}</label>
            <textarea name="store_description" class="input-floating" rows="3">{{ old('store_description', $settings['store_description'] ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Default Currency') }}</label>
                <select name="default_currency" class="input-floating">
                    <option value="usd" {{ (old('default_currency', $settings['default_currency'] ?? 'usd')) === 'usd' ? 'selected' : '' }}>{{ __('USD') }}</option>
                    <option value="sar" {{ (old('default_currency', $settings['default_currency'] ?? 'usd')) === 'sar' ? 'selected' : '' }}>{{ __('SAR') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Default Language') }}</label>
                <select name="default_language" class="input-floating">
                    <option value="en" {{ (old('default_language', $settings['default_language'] ?? 'en')) === 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                    <option value="ar" {{ (old('default_language', $settings['default_language'] ?? 'en')) === 'ar' ? 'selected' : '' }}>{{ __('Arabic') }}</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Exchange Rate') }} (1 USD = ? SAR)</label>
            <input type="number" step="0.0001" name="exchange_rate_usd_to_sar" value="{{ old('exchange_rate_usd_to_sar', $settings['exchange_rate_usd_to_sar'] ?? '3.75') }}" class="input-floating">
        </div>

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Admin Email') }}</label>
            <input type="email" name="admin_email" value="{{ old('admin_email', $settings['admin_email'] ?? '') }}" class="input-floating">
        </div>

        <button class="px-6 py-2.5 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border">{{ __('Save Settings') }}</button>
    </form>
@endsection
