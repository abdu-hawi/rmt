@extends('admin.layouts.admin')

@section('title', __('Edit') . ' - ' . $product->name_en)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-sm text-muted-400 hover:text-accent-light transition-colors">&larr; {{ __('Back') }}</a>
        <h1 class="text-2xl font-bold text-white mt-2">{{ __('Edit') }}: {{ $product->name_en }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="glass-card p-6 max-w-2xl space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Product Name (EN)') }} *</label>
                <input type="text" name="name_en" value="{{ old('name_en', $product->name_en) }}" required class="input-floating">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Product Name (AR)') }} *</label>
                <input type="text" name="name_ar" value="{{ old('name_ar', $product->name_ar) }}" required class="input-floating" style="font-family: 'Cairo', sans-serif;">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Description (EN)') }} *</label>
                <textarea name="description_en" required class="input-floating" rows="4">{{ old('description_en', $product->description_en) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Description (AR)') }} *</label>
                <textarea name="description_ar" required class="input-floating" rows="4" style="font-family: 'Cairo', sans-serif;">{{ old('description_ar', $product->description_ar) }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Price (USD)') }} *</label>
                <input type="number" step="0.01" name="price_usd" value="{{ old('price_usd', $product->price_usd) }}" required class="input-floating">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Price (SAR)') }} *</label>
                <input type="number" step="0.01" name="price_sar" value="{{ old('price_sar', $product->price_sar) }}" required class="input-floating">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Category') }}</label>
            <select name="category_id" class="input-floating">
                <option value="">{{ __('None') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="rounded border-slate-700 bg-carbon-900 text-accent focus:ring-accent/30">
                <span class="text-white">{{ __('Active') }}</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Download URL') }}</label>
            <input type="text" name="download_url" value="{{ old('download_url', $product->download_url) }}" class="input-floating">
        </div>

        <div class="border-t border-slate-700/30 pt-5">
            <h3 class="text-lg font-bold text-white mb-4">{{ __('SEO Settings') }}</h3>
            <div class="grid grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('SEO Title (EN)') }}</label><input type="text" name="seo_title_en" value="{{ old('seo_title_en', $product->seo_title_en) }}" class="input-floating"></div>
                <div><label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('SEO Title (AR)') }}</label><input type="text" name="seo_title_ar" value="{{ old('seo_title_ar', $product->seo_title_ar) }}" class="input-floating"></div>
                <div><label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('SEO Description (EN)') }}</label><textarea name="seo_description_en" class="input-floating" rows="2">{{ old('seo_description_en', $product->seo_description_en) }}</textarea></div>
                <div><label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('SEO Description (AR)') }}</label><textarea name="seo_description_ar" class="input-floating" rows="2">{{ old('seo_description_ar', $product->seo_description_ar) }}</textarea></div>
                <div><label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('SEO Keywords (EN)') }}</label><input type="text" name="seo_keywords_en" value="{{ old('seo_keywords_en', $product->seo_keywords_en) }}" class="input-floating"></div>
                <div><label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('SEO Keywords (AR)') }}</label><input type="text" name="seo_keywords_ar" value="{{ old('seo_keywords_ar', $product->seo_keywords_ar) }}" class="input-floating"></div>
            </div>
        </div>

        <div class="flex items-end gap-5">
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Schema Type') }}</label>
                <input type="text" name="schema_type" value="{{ old('schema_type', $product->schema_type) }}" class="input-floating w-48">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-400 mb-1.5">{{ __('Sort Order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}" class="input-floating w-24">
            </div>
            <button class="px-6 py-2.5 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border">{{ __('Save') }}</button>
        </div>
    </form>
@endsection
