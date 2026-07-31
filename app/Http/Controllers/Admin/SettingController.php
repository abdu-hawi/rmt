<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string',
            'default_currency' => 'required|in:usd,sar',
            'default_language' => 'required|in:en,ar',
            'exchange_rate_usd_to_sar' => 'required|numeric|min:0.01',
            'admin_email' => 'nullable|email',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Log::info('Admin updated settings');

        return redirect()->route('admin.settings.index')->with('success', __('Settings updated'));
    }
}
