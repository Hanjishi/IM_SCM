<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\AppliedDiscount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::with(['appliedDiscounts'])->get();
        return response()->json($promotions);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $promotion = Promotion::create($request->all());
        return response()->json($promotion, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $promotion = Promotion::with(['appliedDiscounts'])->findOrFail($id);
        return response()->json($promotion);
    }

    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'string|max:255',
            'description' => 'string',
            'discount_type' => 'in:percentage,fixed_amount',
            'discount_value' => 'numeric|min:0',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $promotion->update($request->all());
        return response()->json($promotion);
    }

    public function getActivePromotions()
    {
        $now = now();
        $promotions = Promotion::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();
        return response()->json($promotions);
    }

    public function applyPromotion(Request $request)
    {
        $this->validate($request, [
            'promotion_id' => 'required|exists:promotions,id',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'discount_amount' => 'required|numeric|min:0'
        ]);

        $appliedDiscount = AppliedDiscount::create([
            'promotion_id' => $request->promotion_id,
            'sales_order_id' => $request->sales_order_id,
            'discount_amount' => $request->discount_amount
        ]);

        return response()->json($appliedDiscount, Response::HTTP_CREATED);
    }

    public function getAppliedDiscounts($promotionId)
    {
        $appliedDiscounts = AppliedDiscount::where('promotion_id', $promotionId)
            ->with(['salesOrder'])
            ->get();
        return response()->json($appliedDiscounts);
    }
} 