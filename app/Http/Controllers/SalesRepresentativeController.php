<?php

namespace App\Http\Controllers;

use App\Models\SalesRepresentative;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SalesRepresentativeController extends Controller
{
    /**
     * Get all sales representatives with optional filters.
     */
    public function index(Request $request)
    {
        $query = SalesRepresentative::query();

        if ($request->has('region')) {
            $query->where('region', $request->input('region'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $salesReps = $query->paginate($request->input('per_page', 15));

        return response()->json($salesReps);
    }

    /**
     * Get a specific sales representative by ID.
     */
    public function show($id)
    {
        $salesRep = SalesRepresentative::find($id);

        if (!$salesRep) {
            return response()->json(['message' => 'Sales representative not found'], 404);
        }

        return response()->json($salesRep);
    }

    /**
     * Create a new sales representative.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:sales_representatives,email',
                'phone_number' => 'nullable|string|max:50',
                'region' => 'nullable|string|max:100',
                'commission_rate' => 'required|numeric|min:0|max:100',
                'is_active' => 'boolean'
            ]);

            DB::beginTransaction();

            $salesRep = SalesRepresentative::create($request->all());

            DB::commit();

            return response()->json($salesRep, 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create sales representative: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing sales representative.
     */
    public function update(Request $request, $id)
    {
        $salesRep = SalesRepresentative::find($id);

        if (!$salesRep) {
            return response()->json(['message' => 'Sales representative not found'], 404);
        }

        try {
            $this->validate($request, [
                'first_name' => 'string|max:100',
                'last_name' => 'string|max:100',
                'email' => 'email|unique:sales_representatives,email,' . $salesRep->sales_rep_id . ',sales_rep_id',
                'phone_number' => 'nullable|string|max:50',
                'region' => 'nullable|string|max:100',
                'commission_rate' => 'numeric|min:0|max:100',
                'is_active' => 'boolean'
            ]);

            DB::beginTransaction();

            $salesRep->update($request->all());

            DB::commit();

            return response()->json($salesRep);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update sales representative: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a sales representative.
     */
    public function destroy($id)
    {
        $salesRep = SalesRepresentative::find($id);

        if (!$salesRep) {
            return response()->json(['message' => 'Sales representative not found'], 404);
        }

        try {
            DB::beginTransaction();
            $salesRep->delete();
            DB::commit();
            return response()->json(['message' => 'Sales representative deleted successfully'], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete sales representative: ' . $e->getMessage()], 500);
        }
    }
} 