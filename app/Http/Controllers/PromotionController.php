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
            'promotion_name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $promotion = Promotion::create([
            'promotion_name' => $request->input('name'),
            'promotion_type' => $request->input('discount_type'),
            'discount_value' => $request->input('discount_value'),
            'min_order_amount' => $request->input('minimum_purchase'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'is_active' => $request->input('is_active', true),
        ]);

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
            'name' => 'sometimes|string|max:255',
            'discount_type' => 'sometimes|in:percentage,fixed_amount',
            'discount_value' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $promotion->update([
            'promotion_name' => $request->input('name', $promotion->promotion_name),
            'promotion_type' => $request->input('discount_type', $promotion->promotion_type),
            'discount_value' => $request->input('discount_value', $promotion->discount_value),
            'min_order_amount' => $request->input('minimum_purchase', $promotion->min_order_amount),
            'start_date' => $request->input('start_date', $promotion->start_date),
            'end_date' => $request->input('end_date', $promotion->end_date),
            'is_active' => $request->input('is_active', $promotion->is_active),
        ]);

        return response()->json($promotion);
    }

    public function getActivePromotions()
    {
        $now = now();

        $promotions = Promotion::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        if ($promotions->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'No active promotions available at the moment.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Active promotions retrieved successfully.',
            'data' => $promotions
        ], 200);
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