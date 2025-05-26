<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query();
        // Add filters like customer_id, status, date range
        $quotations = $query->with('customer', 'salesRepresentative')->paginate($request->input('per_page', 15));
        return response()->json($quotations);
    }

    public function show($id)
    {
        $quotation = Quotation::with('items.product', 'customer', 'salesRepresentative')->find($id);
        if (!$quotation) {
            return response()->json(['message' => 'Quotation not found'], 404);
        }
        return response()->json($quotation);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'customer_id' => 'required|exists:customers,customer_id',
                'representative_id' => 'nullable|exists:sales_representatives,representative_id',
                'quotation_date' => 'required|date',
                'valid_until' => 'nullable|date|after_or_equal:quotation_date',
                'terms_conditions' => 'nullable|string',
                'items.*.product_id' => 'required|exists:products,product_id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            ]);

            DB::beginTransaction();

            $totalAmount = 0;
            $quotationItemsData = [];

            foreach ($request->input('items') as $itemData) {
                $unitPrice = $itemData['unit_price'];
                $discountPercentage = $itemData['discount_percentage'] ?? 0;
                $lineTotal = ($unitPrice * $itemData['quantity']) * (1 - ($discountPercentage / 100));

                $quotationItemsData[] = [
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercentage,
                    'line_total' => $lineTotal,
                ];
                $totalAmount += $lineTotal;
            }

            $quotation = Quotation::create([
                'customer_id' => $request->input('customer_id'),
                'representative_id' => $request->input('srepresentative_id'),
                'quotation_date' => $request->input('quotation_date'),
                'valid_until' => $request->input('valid_until'),
                'quotation_status' => 'Draft',
                'total_amount' => $totalAmount,
                'terms_conditions' => $request->input('terms_conditions'),
            ]);

            $quotation->items()->createMany($quotationItemsData);

            DB::commit();

            return response()->json($quotation->load('items.product'), 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create quotation: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'customer_id' => 'required|exists:customers,customer_id',
                'representative_id' => 'nullable|exists:sales_representatives,representative_id',
                'quotation_date' => 'required|date',
                'valid_until' => 'nullable|date|after_or_equal:quotation_date',
                'terms_conditions' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,product_id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            ]);

            $quotation = Quotation::find($id);

            if (!$quotation) {
                return response()->json(['message' => 'Quotation not found'], 404);
            }

            DB::beginTransaction();

            // Recalculate total amount and build new items
            $totalAmount = 0;
            $quotationItemsData = [];

            foreach ($request->input('items') as $itemData) {
                $unitPrice = $itemData['unit_price'];
                $discountPercentage = $itemData['discount_percentage'] ?? 0;
                $lineTotal = ($unitPrice * $itemData['quantity']) * (1 - ($discountPercentage / 100));

                $quotationItemsData[] = [
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercentage,
                    'line_total' => $lineTotal,
                ];

                $totalAmount += $lineTotal;
            }

            // Update the main quotation record
            $quotation->update([
                'customer_id' => $request->input('customer_id'),
                'representative_id' => $request->input('representative_id'),
                'quotation_date' => $request->input('quotation_date'),
                'valid_until' => $request->input('valid_until'),
                'total_amount' => $totalAmount,
                'terms_conditions' => $request->input('terms_conditions'),
            ]);

            // Delete old items and insert new ones
            $quotation->items()->delete();
            $quotation->items()->createMany($quotationItemsData);

            DB::commit();

            return response()->json($quotation->load('items.product'), 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update quotation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convert a quotation to a sales order.
     * Rule 2.2: Once accepted, convert to sales order.
     * POST /api/v1/quotations/{id}/convert-to-order
     */
    public function convertToOrder($id)
    {
        $quotation = Quotation::with('items')->find($id);

        if (!$quotation) {
            return response()->json(['message' => 'Quotation not found'], 404);
        }

        if ($quotation->quotation_status === 'Accepted' && $quotation->salesOrder) {
            return response()->json(['message' => 'Quotation already converted to sales order: ' . $quotation->salesOrder->order_id], 409); // Conflict
        }
        if ($quotation->quotation_status === 'Rejected' || $quotation->quotation_status === 'Expired') {
            return response()->json(['message' => 'Cannot convert quotation with status ' . $quotation->quotation_status], 400); // Bad Request
        }

        DB::beginTransaction();
        try {
            // Create Sales Order
            $order = SalesOrder::create([
                'customer_id' => $quotation->customer_id,
                'representative_id' => $quotation->representative_id,
                'quotation_id' => $quotation->quotation_id,
                'order_date' => now()->toDateString(), // Current date as order date
                'delivery_date' => null, // To be set later
                'order_status' => 'Pending',
                'total_amount' => $quotation->total_amount,
                // Copy shipping address from customer's default or quotation if available
                'shipping_address_line1' => $quotation->customer->shipping_address_line1,
                'shipping_address_line2' => $quotation->customer->shipping_address_line2,
                'shipping_city' => $quotation->customer->shipping_city,
                'shipping_state' => $quotation->customer->shipping_state,
                'shipping_zip_code' => $quotation->customer->shipping_zip_code,
                'shipping_country' => $quotation->customer->shipping_country,
                'shipping_cost' => 0.00, // To be calculated or set by shipping logic
                'tax_amount' => 0.00, // To be calculated or set by tax logic
            ]);

            // Create Sales Order Items from Quotation Items
            foreach ($quotation->items as $quotationItem) {
                $order->items()->create([
                    'product_id' => $quotationItem->product_id,
                    'quantity' => $quotationItem->quantity,
                    'unit_price' => $quotationItem->unit_price,
                    'discount_amount' => ($quotationItem->unit_price * $quotationItem->quantity * $quotationItem->discount_percentage / 100),
                    'line_total' => $quotationItem->line_total,
                ]);
            }

            // Update Quotation Status
            $quotation->update(['quotation_status' => 'Accepted']);

            DB::commit();

            return response()->json(['message' => 'Quotation converted to sales order successfully', 'order' => $order->load('items.product')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to convert quotation to order: ' . $e->getMessage()], 500);
        }
    }
}