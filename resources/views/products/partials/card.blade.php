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