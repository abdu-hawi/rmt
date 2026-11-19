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

        $summary = $this->cart->summary();
        $items = collect($summary['lines']);
        $subtotal = $summary['subtotal'];
        $discount = $summary['discount'];
        $taxableSubtotal = $summary['taxable_subtotal'];
        $vat = $summary['vat'];
        $total = $summary['total'];

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
            $summary = $this->cart->summary($currency);
            $subtotal = $summary['subtotal'];
            $discount = $summary['discount'];
            $vat = $summary['vat'];
            $total = $summary['total'];
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

            // Snapshot the tax configuration of every line so later product
            // edits can never rewrite the history of this order.
            foreach ($summary['lines'] as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['id'],
                    'product_name' => $line['name'],
                    'price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'is_tax_inclusive' => $line['is_tax_inclusive'],
                    'net_total' => $line['net_total'],
                    'tax_amount' => $line['tax_amount'],
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            DB::commit();

            Log::info('Order placed', ['order_number' => $order->order_number, 'total' => $total, 'currency' => $currency]);

            // بوابة EdfaPay تعمل بالريال السعودي فقط (SAR-only كما هو موثق في PROJECT_MAP)،
            // لذلك نحوّل الإجمالي إلى SAR عند الشراء بعملة أخرى قبل تمريره للبوابة.
            $gatewayCurrency = CurrencyService::SAR;
            $gatewayAmount = $currency === CurrencyService::SAR
                ? $total
                : CurrencyService::convert((float) $total, CurrencyService::SAR);

            return $this->paymentGateway->paymentProcess([
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'amount'       => $gatewayAmount,
                'currency'     => $gatewayCurrency,
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
