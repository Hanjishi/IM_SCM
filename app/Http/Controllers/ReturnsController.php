<?php

namespace App\Http\Controllers;

use App\Models\Returns;
use App\Models\ReturnItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReturnsController extends Controller
{
        public function index()
        {
            $returns = Returns::with(['customer', 'order', 'items'])->get();
            return response()->json($returns);
        }

        public function store(Request $request)
        {
            $this->validate($request, [
                'order_id' => 'required|exists:sales_orders,order_id',
                'customer_id' => 'required|exists:customers,customer_id',
                'return_date' => 'required|date',
                'reason_for_return' => 'required|string',
                'product_condition' => 'nullable|string',
                'resolution_status' => 'required|in:pending,approved,rejected,completed',
                'refund_amount' => 'nullable|numeric|min:0',
                'refund_method' => 'nullable|string',
                'restocking_fee' => 'nullable|numeric|min:0',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,product_id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.reason' => 'required|string'
            ]);

            $return = Returns::create([
                'order_id' => $request->order_id,
                'customer_id' => $request->customer_id,
                'return_date' => $request->return_date,
                'reason_for_return' => $request->reason_for_return,
                'product_condition' => $request->product_condition,
                'resolution_status' => $request->resolution_status,
                'refund_amount' => $request->refund_amount,
                'refund_method' => $request->refund_method,
                'restocking_fee' => $request->restocking_fee,
            ]);

            foreach ($request->items as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'reason' => $item['reason'],
                ]);
            }

            return response()->json($return->load(['customer', 'order', 'items']), Response::HTTP_CREATED);
        }

        public function show($id)
        {
            $return = Returns::with(['customer', 'order', 'items'])->findOrFail($id);
            return response()->json($return);
        }

        public function destroy($id)
        {
            $return = Returns::with('items.product')->findOrFail($id);

            $return->delete();
            return response()->json(['message' => 'Return deleted successfully']);
        }
}