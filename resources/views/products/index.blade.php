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
                <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @include('products.partials.card', ['products' => $products, 'isAr' => $isAr])
                </div>
                <div id="products-loader" class="hidden mt-8 flex justify-center">
                    <div class="w-8 h-8 border-2 border-accent/30 border-t-accent rounded-full animate-spin"></div>
                </div>
                <div id="products-end" class="hidden mt-8 text-center text-muted-400 text-sm"></div>
                <div id="products-sentinel"></div>
                <noscript>
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                </noscript>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @if(!$products->isEmpty() && $products->hasMorePages())
        <script>
            (function () {
                let loading = false;
                let nextUrl = {!! json_encode($products->nextPageUrl()) !!} || null;
                const sentinel = document.getElementById('products-sentinel');
                const grid = document.getElementById('products-grid');
                const loader = document.getElementById('products-loader');
                const endMsg = document.getElementById('products-end');

                if (!sentinel || !grid || !nextUrl) return;

                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting || loading || !nextUrl) return;
                        loading = true;
                        loader.classList.remove('hidden');

                        fetch(nextUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function (res) { return res.json(); })
                            .then(function (data) {
                                if (data.html) {
                                    grid.insertAdjacentHTML('beforeend', data.html);
                                }
                                nextUrl = data.next_page_url || null;
                                if (!nextUrl) {
                                    observer.disconnect();
                                }
                            })
                            .catch(function () { nextUrl = null; observer.disconnect(); })
                            .finally(function () {
                                loading = false;
                                loader.classList.add('hidden');
                                endMsg.classList.remove('hidden');
                                endMsg.textContent = nextUrl ? '' : {!! json_encode(__('You have reached the end')) !!};
                            });
                    });
                }, { rootMargin: '200px' });

                observer.observe(sentinel);
            })();
        </script>
    @endif
@endsection
