<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Get all audit logs with optional filters.
     */
    public function index(Request $request)
    {
        $query = AuditLog::query();

        if ($request->has('table_name')) {
            $query->where('table_name', $request->input('table_name'));
        }

        if ($request->has('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->has('changed_by')) {
            $query->where('changed_by', $request->input('changed_by'));
        }

        $logs = $query->orderBy('created_at', 'desc')
                     ->paginate($request->input('per_page', 15));

        return response()->json($logs);
    }

    /**
     * Get a specific audit log by ID.
     */
    public function show($id)
    {
        $log = AuditLog::find($id);

        if (!$log) {
            return response()->json(['message' => 'Audit log not found'], 404);
        }

        return response()->json($log);
    }
} 