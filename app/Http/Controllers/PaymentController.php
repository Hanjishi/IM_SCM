<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    // Store a new payment linked to an invoice
    public function store(Request $request, $invoice_id)
    {
        $invoice = Invoice::findOrFail($invoice_id);

        $this->validate($request, [
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'transaction_id' => 'required|string',
        ]);

        $payment = new Payment($request->all());
        $payment->invoice_id = $invoice->invoice_id;
        $payment->save();

        // Update invoice status if fully paid
        $totalPaid = $invoice->payments()->sum('payment_amount');
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->invoice_status = 'paid';
            $invoice->save();
        }

        return response()->json($payment, Response::HTTP_CREATED);
    }

    // Get all payments for an invoice
    public function index($invoice_id)
    {
        $invoice = Invoice::findOrFail($invoice_id);
        $payments = $invoice->payments;
        return response()->json($payments);
    }
}
