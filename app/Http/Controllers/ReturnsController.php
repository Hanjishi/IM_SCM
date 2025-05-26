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
        $returns = Returns::with(['customer', 'salesOrder', 'items'])->get();
        return response()->json($returns);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'return_reason' => 'required|string',
            'return_date' => 'required|date',
            'status' => 'required|in:pending,approved,rejected,completed',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.reason' => 'required|string'
        ]);

        $return = Returns::create([
            'sales_order_id' => $request->sales_order_id,
            'customer_id' => $request->customer_id,
            'return_reason' => $request->return_reason,
            'return_date' => $request->return_date,
            'status' => $request->status
        ]);

        foreach ($request->items as $item) {
            $return->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'reason' => $item['reason']
            ]);
        }

        return response()->json($return->load('items'), Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $return = Returns::with(['customer', 'salesOrder', 'items'])->findOrFail($id);
        return response()->json($return);
    }

    public function updateStatus(Request $request, $id)
    {
        $return = Returns::findOrFail($id);
        
        $this->validate($request, [
            'status' => 'required|in:pending,approved,rejected,completed'
        ]);

        $return->status = $request->status;
        $return->save();

        // If approved, update inventory
        if ($request->status === 'approved') {
            foreach ($return->items as $item) {
                $product = $item->product;
                $product->stock_quantity += $item->quantity;
                $product->save();
            }
        }

        return response()->json($return->load('items'));
    }

    public function getByCustomer($customerId)
    {
        $returns = Returns::where('customer_id', $customerId)
            ->with(['salesOrder', 'items'])
            ->get();
        return response()->json($returns);
    }

    public function getBySalesOrder($salesOrderId)
    {
        $returns = Returns::where('sales_order_id', $salesOrderId)
            ->with(['customer', 'items'])
            ->get();
        return response()->json($returns);
    }
} 