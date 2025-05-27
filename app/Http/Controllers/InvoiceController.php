<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function index()
    {
        try {
        $invoices = Invoice::with(['customer', 'salesOrder', 'payments'])->get();
        return response()->json($invoices);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store a new invoice.
     * Rule 4.2: Invoice creation with validation.
     * POST /api/v1/invoices
     */
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required|exists:sales_orders,order_id',
            'customer_id' => 'required|exists:customers,customer_id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'invoice_status' => 'required|in:pending,paid,overdue,cancelled'
        ]);

        try {
            $invoice = Invoice::create([
                'order_id' => $request->order_id,
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'total_amount' => $request->total_amount,
                'amount_paid' => 0, // default to 0 on creation
                'invoice_status' => $request->invoice_status,
            ]);

            return response()->json($invoice, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }

        if (Invoice::where('order_id', $request->order_id)->exists()) {
            return response()->json([
                'message' => 'Invoice already exists for this order.'
            ], 409); // Conflict
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'salesOrder', 'payments'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $this->validate($request, [
            'status' => 'in:pending,paid,overdue,cancelled',
            'due_date' => 'date'
        ]);

        $invoice->update($request->all());
        return response()->json($invoice);
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $this->validate($request, [
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'transaction_id' => 'required|string',
        ]);

        $payment = new Payment($request->all());
        $invoice->payments()->save($payment);

        // Update total amount_paid on invoice
        $totalPaid = $invoice->payments()->sum('payment_amount');
        $invoice->amount_paid = $totalPaid;

        if ($totalPaid >= $invoice->total_amount) {
            $invoice->invoice_status = 'paid';
        }

        $invoice->save();

        return response()->json($payment, Response::HTTP_CREATED);
    }

    public function getPayments($id)
    {
        $invoice = Invoice::findOrFail($id);
        $payments = $invoice->payments;
        return response()->json($payments);
    }

    public function getOverdueInvoices()
    {
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->where('due_date', '<', now())
            ->with(['customer', 'salesOrder'])
            ->get();
        return response()->json($overdueInvoices);
    }
}
