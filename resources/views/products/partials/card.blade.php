@foreach($products as $product)
    <div class="group glass-card overflow-hidden hover:glow-border transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
            <a href="{{ route('products.show', $product->slug) }}" class="block">
                <div class="flex items-start justify-between mb-4">
                    <span class="tech-tag">{{ __($product->type) }}</span>
                    <span class="px-2 py-0.5 text-[10px] font-mono text-violet-accent bg-violet-accent/10 border border-violet-accent/20 rounded">{{ $product->schema_type }}</span>
                </div>
                <h2 class="text-lg font-bold text-white group-hover:text-accent-light transition-colors mb-2">{{ $isAr ? $product->name_ar : $product->name_en }}</h2>
                <p class="text-sm text-muted-400 line-clamp-2 mb-4 leading-relaxed">{{ $isAr ? $product->description_ar : $product->description_en }}</p>
            </a>

            <div class="pt-4 border-t border-slate-700/30 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex flex-col items-start gap-1">
                        <span class="text-xl font-bold text-accent-light">{{ format_price($product->price_usd) }}</span>
                        @if($product->is_tax_inclusive)
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium text-success bg-success/10 border border-success/20 rounded-full">{{ __('Tax Included') }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-muted-400">{{ __('Instant Download') }}</span>
                </div>

                <form method="POST" action="{{ route('cart.add') }}" data-ajax-add="1" class="block">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="add-to-cart-btn w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-accent-light bg-accent/10 border border-accent/30 rounded-lg hover:bg-accent hover:text-white transition-all">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        <span>{{ __('Add to Cart') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach
