<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('mgteam.products.coupons', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        Coupon::create(array_merge($validated, ['parent_id' => parentId()]));

        return back()->with('success', 'Cupom criado.');
    }
}
