<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CurrencyService;
use App\Services\SeoService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $locale = app()->getLocale();
            $nameCol = $locale === 'ar' ? 'name_ar' : 'name_en';
            $descCol = $locale === 'ar' ? 'description_ar' : 'description_en';
            $query->where($nameCol, 'like', "%{$search}%")
                  ->orWhere($descCol, 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->input('category')));
        }

        $products = $query->orderBy('sort_order')->paginate(12);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        $seo = SeoService::metaTags($product);
        $schema = SeoService::schemaJsonLd($product);

        return view('products.show', compact('product', 'related', 'seo', 'schema'));
    }
}
