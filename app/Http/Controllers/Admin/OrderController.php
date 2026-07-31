<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('payer_email', 'like', "%{$search}%")
                  ->orWhere('payer_first_name', 'like', "%{$search}%")
                  ->orWhere('payer_last_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order->update($validated);

        Log::info('Admin updated order status', [
            'order_number' => $order->order_number,
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', __('Order status updated'));
    }
}
