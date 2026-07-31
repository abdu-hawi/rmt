@php
    $seo = \App\Services\SeoService::metaTags(null, ['title' => __('Products') . ' - ' . config('seo.site_name')]);
    $isAr = app()->getLocale() === 'ar';
@endphp

@extends($isAr ? 'layouts.app-rtl' : 'layouts.app')

@section('content')
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-accent/5 via-transparent to-transparent pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-8">
            <div class="text-center mb-10">
                <h1 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-white via-white to-muted-400 bg-clip-text text-transparent">{{ __('Products') }}</h1>
                <p class="mt-3 text-muted-400 max-w-xl mx-auto">{{ __('Premium digital solutions for modern enterprises') }}</p>
            </div>

            <div class="flex flex-col md:flex-row gap-4 mb-8">
                <form method="GET" action="{{ route('products.index') }}" class="flex-1 flex gap-2">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search') }}..." class="input-floating pl-10">
                    </div>
                    <button class="px-5 py-2.5 bg-accent hover:bg-accent/90 text-white text-sm font-semibold rounded-lg transition-all glow-border">{{ __('Search') }}</button>
                </form>
            </div>

            <div class="flex flex-wrap gap-2 mb-8">
                <a href="{{ route('products.index') }}" class="px-3 py-1.5 text-xs font-medium rounded-full transition-all {{ !request('category') ? 'bg-accent/20 text-accent-light border border-accent/30' : 'bg-carbon-900/60 text-muted-400 border border-slate-700/30 hover:border-accent/30 hover:text-white' }}">{{ __('All') }}</a>
                @foreach($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full transition-all {{ request('category') === $category->slug ? 'bg-accent/20 text-accent-light border border-accent/30' : 'bg-carbon-900/60 text-muted-400 border border-slate-700/30 hover:border-accent/30 hover:text-white' }}">
                        {{ $isAr ? $category->name_ar : $category->name_en }}
                    </a>
                @endforeach
            </div>

            @if($products->isEmpty())
                <div class="text-center py-20">
                    <svg class="w-16 h-16 mx-auto text-muted-400/30 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-muted-400">{{ __('No products found') }}</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-4 text-sm text-accent-light hover:text-accent">{{ __('Clear filters') }}</a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="group glass-card overflow-hidden hover:glow-border transition-all duration-300 hover:-translate-y-1">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <span class="tech-tag">{{ __($product->type) }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-mono text-violet-accent bg-violet-accent/10 border border-violet-accent/20 rounded">{{ $product->schema_type }}</span>
                                </div>
                                <h2 class="text-lg font-bold text-white group-hover:text-accent-light transition-colors mb-2">{{ $isAr ? $product->name_ar : $product->name_en }}</h2>
                                <p class="text-sm text-muted-400 line-clamp-2 mb-4 leading-relaxed">{{ $isAr ? $product->description_ar : $product->description_en }}</p>
                                <div class="flex items-center justify-between pt-4 border-t border-slate-700/30">
                                    <span class="text-xl font-bold text-accent-light">{{ format_price($product->price_usd) }}</span>
                                    <span class="text-xs text-muted-400">{{ __('Instant Download') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
