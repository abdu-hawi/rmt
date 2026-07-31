<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->orderBy('sort_order')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name_en')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'price_usd' => 'required|numeric|min:0',
            'price_sar' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'download_url' => 'nullable|string|max:500',
            'seo_title_en' => 'nullable|string|max:255',
            'seo_title_ar' => 'nullable|string|max:255',
            'seo_description_en' => 'nullable|string',
            'seo_description_ar' => 'nullable|string',
            'seo_keywords_en' => 'nullable|string',
            'seo_keywords_ar' => 'nullable|string',
            'schema_type' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['type'] = 'digital';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->input('sort_order', 0);

        $product = Product::create($validated);

        Log::info('Admin created product', ['product_id' => $product->id, 'name' => $product->name_en]);

        return redirect()->route('admin.products.index')->with('success', __('Product created'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name_en')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'price_usd' => 'required|numeric|min:0',
            'price_sar' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'download_url' => 'nullable|string|max:500',
            'seo_title_en' => 'nullable|string|max:255',
            'seo_title_ar' => 'nullable|string|max:255',
            'seo_description_en' => 'nullable|string',
            'seo_description_ar' => 'nullable|string',
            'seo_keywords_en' => 'nullable|string',
            'seo_keywords_ar' => 'nullable|string',
            'schema_type' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
        ]);

        if ($request->filled('name_en') && $validated['name_en'] !== $product->name_en) {
            $validated['slug'] = Str::slug($validated['name_en']);
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update($validated);

        Log::info('Admin updated product', ['product_id' => $product->id]);

        return redirect()->route('admin.products.index')->with('success', __('Product updated'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        Log::info('Admin deleted product', ['product_id' => $product->id]);

        return redirect()->route('admin.products.index')->with('success', __('Product deleted'));
    }
}
