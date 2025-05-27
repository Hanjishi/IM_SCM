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
        $deliveryNotes = DeliveryNote::with(['order', 'customer'])->get();
        return response()->json($deliveryNotes);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required|exists:sales_orders,order_id',
            'delivery_date' => 'required|date',
            'shipper' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:255',
            'delivery_status' => 'required|in:Prepared,Shipped,Delivered,Canceled'
        ]);

        $deliveryNotes = DeliveryNote::create($request->only([
            'order_id',
            'delivery_date',
            'shipper',
            'tracking_number',
            'delivery_status'
        ]));

        return response()->json($deliveryNotes, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $deliveryNotes = DeliveryNote::with(['order', 'customer'])->findOrFail($id);
        return response()->json($deliveryNotes);
    }

    public function update(Request $request, $id)
    {
        $deliveryNotes = DeliveryNote::findOrFail($id);
        
        $this->validate($request, [
            'delivery_date' => 'date',
            'delivery_status' => 'in:pending,delivered,cancelled',
        ]);

        $deliveryNotes->update($request->all());
        return response()->json($deliveryNotes);
    }
} 