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
        $invoices = Invoice::with(['customer', 'salesOrder', 'payments'])->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue,cancelled'
        ]);

        $invoice = Invoice::create($request->all());
        return response()->json($invoice, Response::HTTP_CREATED);
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
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'required|string'
        ]);

        $payment = new Payment($request->all());
        $invoice->payments()->save($payment);

        // Update invoice status if fully paid
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->save();
        }

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
