<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['customer', 'salesOrder'])->get();
        return response()->json($complaints);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'complaint_type' => 'required|string',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $complaint = Complaint::create($request->all());
        return response()->json($complaint, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $complaint = Complaint::with(['customer', 'salesOrder'])->findOrFail($id);
        return response()->json($complaint);
    }

    public function updateStatus(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        
        $this->validate($request, [
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'required_if:status,resolved,closed|string'
        ]);

        $complaint->status = $request->status;
        if ($request->has('resolution_notes')) {
            $complaint->resolution_notes = $request->resolution_notes;
        }
        $complaint->save();

        return response()->json($complaint);
    }

    public function getByCustomer($customerId)
    {
        $complaints = Complaint::where('customer_id', $customerId)
            ->with(['salesOrder'])
            ->get();
        return response()->json($complaints);
    }

    public function getByPriority($priority)
    {
        $this->validate(['priority' => $priority], [
            'priority' => 'required|in:low,medium,high'
        ]);

        $complaints = Complaint::where('priority', $priority)
            ->with(['customer', 'salesOrder'])
            ->get();
        return response()->json($complaints);
    }

    public function getOpenComplaints()
    {
        $complaints = Complaint::whereIn('status', ['open', 'in_progress'])
            ->with(['customer', 'salesOrder'])
            ->get();
        return response()->json($complaints);
    }
} 