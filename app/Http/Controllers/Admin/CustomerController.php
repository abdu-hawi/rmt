<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        $orders = Order::where('user_id', $customer->id)->latest()->get();
        return view('admin.customers.show', compact('customer', 'orders'));
    }
}
