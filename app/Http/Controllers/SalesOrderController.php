<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    /**
     * Get all sales orders.
     * Rule 4.1: Real-time updates on status.
     * GET /api/v1/orders?status=Pending&customer_id=1
     */
    public function index(Request $request)
    {
        $query = SalesOrder::query();

        if ($request->has('status')) {
            $query->where('order_status', $request->input('status'));
        }
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if ($request->has('sales_rep_id')) {
            $query->where('sales_rep_id', $request->input('sales_rep_id'));
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('order_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        // Eager load relationships for list view
        $orders = $query->with('customer', 'salesRepresentative')->paginate($request->input('per_page', 15));

        return response()->json($orders);
    }

    /**
     * Get a specific sales order by ID.
     * GET /api/v1/orders/{id}
     */
    public function show($id)
    {
        // Eager load items, customer, and sales representative
        $order = SalesOrder::with('items.product', 'customer', 'salesRepresentative')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Sales Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * Create a new sales order or convert from a quotation.
     * Rule 2.2: Quotation conversion.
     * Rule 2.6: Auto apply taxes, shipping, discounts (simplified here).
     * POST /api/v1/orders
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'customer_id' => 'required|exists:customers,customer_id',
                'sales_rep_id' => 'nullable|exists:sales_representatives,sales_rep_id',
                'quotation_id' => 'nullable|unique:sales_orders,quotation_id|exists:quotations,quotation_id',
                'order_date' => 'required|date',
                'delivery_date' => 'nullable|date|after_or_equal:order_date',
                'shipping_address_line1' => 'required|string|max:255',
                'shipping_city' => 'required|string|max:100',
                'shipping_state' => 'required|string|max:100',
                'shipping_zip_code' => 'required|string|max:20',
                'shipping_country' => 'required|string|max:100',
                'shipping_cost' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,product_id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount_amount' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($request->input('items') as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product with ID {$itemData['product_id']} not found.");
                }

                $unitPrice = $itemData['unit_price'] ?? $product->unit_price; // Use provided price or product's default
                $discountAmount = $itemData['discount_amount'] ?? 0;
                $lineTotal = ($unitPrice * $itemData['quantity']) - $discountAmount;

                $orderItemsData[] = [
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                ];
                $totalAmount += $lineTotal;
            }

            // Add shipping and tax to total (Rule 2.6)
            $totalAmount += ($request->input('shipping_cost', 0) + $request->input('tax_amount', 0));

            $order = SalesOrder::create([
                'customer_id' => $request->input('customer_id'),
                'representative_id' => $request->input('representative_id'),
                'quotation_id' => $request->input('quotation_id'),
                'order_date' => $request->input('order_date'),
                'delivery_date' => $request->input('delivery_date'),
                'order_status' => 'Pending', // Initial status (Rule 2.3)
                'total_amount' => $totalAmount,
                'shipping_address_line1' => $request->input('shipping_address_line1'),
                'shipping_address_line2' => $request->input('shipping_address_line2'),
                'shipping_city' => $request->input('shipping_city'),
                'shipping_state' => $request->input('shipping_state'),
                'shipping_zip_code' => $request->input('shipping_zip_code'),
                'shipping_country' => $request->input('shipping_country'),
                'shipping_cost' => $request->input('shipping_cost', 0),
                'tax_amount' => $request->input('tax_amount', 0),
            ]);

            $order->items()->createMany($orderItemsData);

            // Update quotation status if converted (Rule 2.2)
            if ($request->has('quotation_id')) {
                Quotation::where('quotation_id', $request->input('quotation_id'))
                    ->update(['quotation_status' => 'Accepted']);
            }

            // TODO: Apply promotions/loyalty points based on business rules here (Rule 3.1, 3.2, 3.3)
            // This would involve checking promotion/coupon rules and adjusting total_amount or applying AppliedDiscount records.

            DB::commit();

            return response()->json($order->load('items.product', 'customer'), 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create sales order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the status of a sales order.
     * Rule 4.2: Status updated immediately.
     * Rule 4.3: Internal teams and customers notified (handled by events/queues in real app).
     * PUT /api/v1/orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = SalesOrder::find($id);

        if (!$order) {
            return response()->json(['message' => 'Sales Order not found'], 404);
        }

        try {
            $this->validate($request, [
                'order_status' => 'required|string|in:Pending,Confirmed,Partially Shipped,Shipped,Invoiced,Canceled',
            ]);

            $oldStatus = $order->order_status;
            $newStatus = $request->input('order_status');

            if ($oldStatus === $newStatus) {
                return response()->json(['message' => 'Order status is already ' . $newStatus], 200);
            }

            $order->order_status = $newStatus;
            $order->save();

            // Rule 4.3: Notification logic (simplified)
            // In a real application, you would dispatch an event here
            // e.g., event(new OrderStatusChanged($order, $oldStatus, $newStatus));
            // And a listener would send emails/notifications.
            // For now, we'll just log it or return a success message.

            return response()->json(['message' => 'Order status updated successfully', 'order' => $order]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update order status: ' . $e->getMessage()], 500);
        }
    }

    // TODO: Add more methods for updating order details, deleting orders, etc.
}