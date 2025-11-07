<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Coupon::latest()->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        $coupon = Coupon::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Coupon created successfully',
            'data' => $coupon
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        return response()->json([
            'status' => true,
            'data' => $coupon
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'sometimes|in:fixed,percent',
            'value' => 'sometimes|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $coupon->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Coupon updated successfully',
            'data' => $coupon
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'status' => true,
            'message' => 'Coupon deleted successfully'
        ]);
    }

    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $validated['code'])->first();

        if (! $coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid coupon code.',
            ], 404);
        }

        if (! $coupon->isValid()) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon is expired, inactive, or usage limit has been reached.',
            ], 400);
        }

        $discount = $coupon->calculateDiscount($validated['amount']);
        $finalAmount = max(0, round($validated['amount'] - $discount, 2));

        $coupon->incrementUsage();

        return response()->json([
            'status' => true,
            'message' => 'Coupon applied successfully.',
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'max_discount' => $coupon->max_discount,
                'usage_limit' => $coupon->usage_limit,
                'used_count' => $coupon->used_count,
            ]
        ]);
    }
}
