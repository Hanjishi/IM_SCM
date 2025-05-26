<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryNoteController extends Controller
{
    public function index()
    {
        $deliveryNotes = DeliveryNote::with(['salesOrder', 'customer'])->get();
        return response()->json($deliveryNotes);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_date' => 'required|date',
            'delivery_address' => 'required|string',
            'status' => 'required|in:pending,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        $deliveryNote = DeliveryNote::create($request->all());
        return response()->json($deliveryNote, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $deliveryNote = DeliveryNote::with(['salesOrder', 'customer'])->findOrFail($id);
        return response()->json($deliveryNote);
    }

    public function update(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        
        $this->validate($request, [
            'delivery_date' => 'date',
            'delivery_address' => 'string',
            'status' => 'in:pending,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        $deliveryNote->update($request->all());
        return response()->json($deliveryNote);
    }

    public function getBySalesOrder($salesOrderId)
    {
        $deliveryNotes = DeliveryNote::where('sales_order_id', $salesOrderId)
            ->with(['salesOrder', 'customer'])
            ->get();
        return response()->json($deliveryNotes);
    }

    public function updateStatus(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        
        $this->validate($request, [
            'status' => 'required|in:pending,delivered,cancelled'
        ]);

        $deliveryNote->status = $request->status;
        $deliveryNote->save();

        // If delivered, update sales order status
        if ($request->status === 'delivered') {
            $salesOrder = SalesOrder::find($deliveryNote->sales_order_id);
            if ($salesOrder) {
                $salesOrder->status = 'delivered';
                $salesOrder->save();
            }
        }

        return response()->json($deliveryNote);
    }
} 