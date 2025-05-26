<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyProgram;
use App\Models\CustomerLoyaltyPoint;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoyaltyProgramController extends Controller
{
    public function index()
    {
        $programs = LoyaltyProgram::with(['customers'])->get();
        return response()->json($programs);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'points_per_currency' => 'required|numeric|min:0',
            'minimum_points_redemption' => 'required|integer|min:1',
            'points_value' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $program = LoyaltyProgram::create($request->all());
        return response()->json($program, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $program = LoyaltyProgram::with(['customers'])->findOrFail($id);
        return response()->json($program);
    }

    public function update(Request $request, $id)
    {
        $program = LoyaltyProgram::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'string|max:255',
            'description' => 'string',
            'points_per_currency' => 'numeric|min:0',
            'minimum_points_redemption' => 'integer|min:1',
            'points_value' => 'numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $program->update($request->all());
        return response()->json($program);
    }

    public function getCustomerPoints($customerId)
    {
        $loyaltyPoints = CustomerLoyaltyPoint::where('customer_id', $customerId)
            ->with(['loyaltyProgram', 'transactions'])
            ->get();
        return response()->json($loyaltyPoints);
    }

    public function addPoints(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:customers,id',
            'loyalty_program_id' => 'required|exists:loyalty_programs,id',
            'points' => 'required|integer|min:1',
            'transaction_type' => 'required|in:purchase,redemption,adjustment',
            'reference_id' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $loyaltyPoint = CustomerLoyaltyPoint::firstOrCreate([
            'customer_id' => $request->customer_id,
            'loyalty_program_id' => $request->loyalty_program_id
        ]);

        $transaction = new PointTransaction([
            'points' => $request->points,
            'transaction_type' => $request->transaction_type,
            'reference_id' => $request->reference_id,
            'notes' => $request->notes
        ]);

        $loyaltyPoint->transactions()->save($transaction);

        // Update total points
        if ($request->transaction_type === 'purchase') {
            $loyaltyPoint->total_points += $request->points;
        } elseif ($request->transaction_type === 'redemption') {
            $loyaltyPoint->total_points -= $request->points;
        }
        $loyaltyPoint->save();

        return response()->json($transaction, Response::HTTP_CREATED);
    }

    public function getPointTransactions($customerId, $programId)
    {
        $transactions = PointTransaction::whereHas('loyaltyPoint', function ($query) use ($customerId, $programId) {
            $query->where('customer_id', $customerId)
                  ->where('loyalty_program_id', $programId);
        })->get();
        return response()->json($transactions);
    }

    public function calculateRedemptionValue(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:customers,id',
            'loyalty_program_id' => 'required|exists:loyalty_programs,id',
            'points_to_redeem' => 'required|integer|min:1'
        ]);

        $loyaltyPoint = CustomerLoyaltyPoint::where('customer_id', $request->customer_id)
            ->where('loyalty_program_id', $request->loyalty_program_id)
            ->firstOrFail();

        $program = LoyaltyProgram::findOrFail($request->loyalty_program_id);

        if ($loyaltyPoint->total_points < $request->points_to_redeem) {
            return response()->json([
                'error' => 'Insufficient points',
                'available_points' => $loyaltyPoint->total_points
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->points_to_redeem < $program->minimum_points_redemption) {
            return response()->json([
                'error' => 'Points below minimum redemption threshold',
                'minimum_points' => $program->minimum_points_redemption
            ], Response::HTTP_BAD_REQUEST);
        }

        $redemptionValue = $request->points_to_redeem * $program->points_value;

        return response()->json([
            'points_to_redeem' => $request->points_to_redeem,
            'redemption_value' => $redemptionValue,
            'available_points' => $loyaltyPoint->total_points
        ]);
    }
} 