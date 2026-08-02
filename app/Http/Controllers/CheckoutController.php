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
        protected CartService $cart,
        protected PaymentGatewayController $paymentGateway
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

            Log::info('Order placed', ['order_number' => $order->order_number, 'total' => $total, 'currency' => $currency]);

            // 1. تحويل مبلغ الطلب إلى ريال سعودي دائماً لأن بوابة الدفع تقبل SAR فقط
            //    (Order::order_number يُستخدم للعلاقات مع الطرف الثالث، و Order::id للعلاقات الداخلية)
            $amountSar = $currency === CurrencyService::SAR
                ? $total
                : CurrencyService::convert($total, CurrencyService::SAR);

            // 2. تمرير بيانات الطلب إلى بوابة الدفع (begin_checkout)
            return $this->paymentGateway->paymentProcess([
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'amount'       => $amountSar,
                'description'  => __('Order') . ' #' . $order->order_number,
                'email'        => $validated['payer_email'],
                'address'      => $validated['payer_address'],
                'city_name'    => $validated['payer_city'],
                'phoneNumber'  => $validated['payer_phone'],
                'postal'       => $validated['payer_zip'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('An error occurred. Please try again.'));
        }
    }

    public function processing(Request $request, $order_number)
    {
        $order = Order::where('order_number', $order_number)->firstOrFail();

        return view('checkout.processing', compact('order', 'order_number'));
    }
}
