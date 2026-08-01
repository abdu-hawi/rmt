<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $discount = $this->cart->discount();
        $vat = $this->cart->vat();
        $total = $this->cart->total();
        $coupon = $this->cart->appliedCoupon();

        return view('cart.index', compact('items', 'subtotal', 'discount', 'vat', 'total', 'coupon'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
        ]);

        $product = Product::findOrFail($request->product_id);
        $this->cart->add($product, $request->quantity ?? 1);

        return redirect()->back()->with('success', __('Product added to cart'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $this->cart->update($request->product_id, $request->quantity);

        return redirect()->route('cart.index')->with('success', __('Cart updated'));
    }

    public function remove($id)
    {
        $this->cart->remove((int) $id);
        return redirect()->route('cart.index')->with('success', __('Product removed from cart'));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $result = $this->cart->applyCoupon($request->code);
        return redirect()->route('cart.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function removeCoupon()
    {
        $this->cart->removeCoupon();
        return redirect()->route('cart.index')->with('success', __('Coupon removed'));
    }
}
