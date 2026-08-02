<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $discount = $this->cart->discount();
        $taxableSubtotal = $this->cart->taxableSubtotal();
        $vat = $this->cart->vat();
        $total = $this->cart->total();

        return view('checkout.index', compact('items', 'subtotal', 'discount', 'taxableSubtotal', 'vat', 'total'));
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $validated = $request->validate([
            'payer_first_name' => 'required|string|max:100',
            'payer_last_name' => 'required|string|max:100',
            'payer_address' => 'required|string|max:500',
            'payer_country' => 'required|string|max:100',
            'payer_city' => 'required|string|max:100',
            'payer_email' => 'required|email|max:255',
            'payer_phone' => 'required|string|max:50',
            'payer_zip' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $currency = CurrencyService::current();
            $subtotal = $this->cart->subtotal($currency);
            $discount = $this->cart->discount($currency);
            $taxableSubtotal = $this->cart->taxableSubtotal($currency);
            $vat = $this->cart->vat($currency);
            $total = $this->cart->total($currency);
            $coupon = $this->cart->appliedCoupon();

            $order = Order::create([
                'user_id' => Auth::id(),
                'currency' => $currency,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat' => $vat,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                ...$validated,
            ]);

            foreach ($this->cart->items() as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $currency === CurrencyService::SAR
                        ? CurrencyService::convert($item['price_usd'], CurrencyService::SAR)
                        : $item['price_usd'],
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            DB::commit();
            $this->cart->clear();

            Log::info('Order placed', ['order_number' => $order->order_number, 'total' => $total, 'currency' => $currency]);

            return redirect()->route('orders.confirmation', $order->order_number)
                ->with('success', __('Order placed successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('An error occurred. Please try again.'));
        }
    }

    public function processing(Request $request, $order_id)
    {
        return view('checkout.processing', compact('order_id'));
    }
}
