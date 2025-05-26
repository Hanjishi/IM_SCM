<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\AppliedDiscount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with(['appliedDiscounts'])->get();
        return response()->json($coupons);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string|unique:coupons,code',
            'description' => 'required|string',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $coupon = Coupon::create($request->all());
        return response()->json($coupon, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $coupon = Coupon::with(['appliedDiscounts'])->findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $this->validate($request, [
            'code' => 'string|unique:coupons,code,' . $id,
            'description' => 'string',
            'discount_type' => 'in:percentage,fixed_amount',
            'discount_value' => 'numeric|min:0',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $coupon->update($request->all());
        return response()->json($coupon);
    }

    public function validateCoupon(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string',
            'purchase_amount' => 'required|numeric|min:0'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid or expired coupon'], Response::HTTP_BAD_REQUEST);
        }

        if ($coupon->minimum_purchase && $request->purchase_amount < $coupon->minimum_purchase) {
            return response()->json([
                'error' => 'Minimum purchase amount not met',
                'minimum_purchase' => $coupon->minimum_purchase
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($coupon->usage_limit) {
            $usageCount = AppliedDiscount::where('coupon_id', $coupon->id)->count();
            if ($usageCount >= $coupon->usage_limit) {
                return response()->json(['error' => 'Coupon usage limit reached'], Response::HTTP_BAD_REQUEST);
            }
        }

        $discountAmount = $this->calculateDiscount($coupon, $request->purchase_amount);
        
        return response()->json([
            'coupon' => $coupon,
            'discount_amount' => $discountAmount
        ]);
    }

    public function getActiveCoupons()
    {
        $now = now();
        $coupons = Coupon::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();
        return response()->json($coupons);
    }

    private function calculateDiscount($coupon, $purchaseAmount)
    {
        $discount = $coupon->discount_type === 'percentage' 
            ? ($purchaseAmount * $coupon->discount_value / 100)
            : $coupon->discount_value;

        if ($coupon->maximum_discount) {
            $discount = min($discount, $coupon->maximum_discount);
        }

        return $discount;
    }
} 