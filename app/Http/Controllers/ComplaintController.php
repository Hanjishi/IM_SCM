<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['customer', 'order', 'product'])->get();
        return response()->json($complaints);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:customers,customer_id',
            'order_id' => 'nullable|exists:sales_orders,order_id',
            'complaint_type' => 'required|string',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'resolution_status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $complaint = Complaint::create($request->all());
        return response()->json($complaint, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $complaint = Complaint::with(['customer', 'order', 'product'])->findOrFail($id);
        return response()->json($complaint);
    }

    public function updateStatus(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $this->validate($request, [
            'resolution_status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_details' => 'required_if:resolution_status,resolved,closed|string'
        ]);

        $complaint->resolution_status = $request->resolution_status;

        if ($request->has('resolution_details')) {
            $complaint->resolution_details = $request->resolution_details;
        }

        $complaint->save();

        return response()->json($complaint);
    }
}
