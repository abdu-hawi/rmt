<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\SeoService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function confirmation($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();
        $seo = SeoService::metaTags(null, ['title' => __('Order Confirmation') . ' - ' . $orderNumber]);

        return view('orders.confirmation', compact('order', 'seo'));
    }

    public function lookup(Request $request)
    {
        $request->validate(['order_number' => 'required|string']);

        $order = Order::where('order_number', $request->order_number)->with('items')->first();

        if (!$order) {
            return redirect()->back()->with('error', __('Order not found'));
        }

        return redirect()->route('orders.confirmation', $order->order_number);
    }
}
