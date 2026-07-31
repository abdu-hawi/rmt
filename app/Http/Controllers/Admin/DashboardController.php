<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total'),
            'total_customers' => User::count(),
            'recent_orders' => Order::with('items')->latest()->take(5)->get(),
            'orders_by_status' => [
                'pending' => Order::where('status', 'pending')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ],
        ];

        Log::info('Admin dashboard viewed');

        return view('admin.dashboard.index', compact('stats'));
    }
}
