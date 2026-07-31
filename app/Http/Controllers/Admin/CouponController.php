<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $coupon = Coupon::create($validated);

        Log::info('Admin created coupon', ['code' => $coupon->code]);

        return redirect()->route('admin.coupons.index')->with('success', __('Coupon created'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => "required|string|max:50|unique:coupons,code,{$coupon->id}",
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $coupon->update($validated);

        Log::info('Admin updated coupon', ['code' => $coupon->code]);

        return redirect()->route('admin.coupons.index')->with('success', __('Coupon updated'));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        Log::info('Admin deleted coupon', ['code' => $coupon->code]);

        return redirect()->route('admin.coupons.index')->with('success', __('Coupon deleted'));
    }
}
