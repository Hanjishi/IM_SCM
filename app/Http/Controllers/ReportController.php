<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\ProductPriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // For date manipulation

class ReportController extends Controller
{
    /**
     * Generate sales trends visualization data.
     * Rule 6.1: Various formats (data for graphs).
     * Rule 6.2: Segmentation by product category, region, customer type, sales rep.
     * GET /api/v1/reports/sales-trends?period=monthly&start_date=2024-01-01&end_date=2024-12-31&category_id=1&region=Luzon
     */
    public function salesTrends(Request $request)
    {
        try {
            $this->validate($request, [
                'period' => 'required|in:daily,weekly,monthly,annually',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'product_category_id' => 'nullable|exists:product_categories,product_category_id',
                'region' => 'nullable|string',
                'customer_type' => 'nullable|string',
                'representative_id' => 'nullable|exists:sales_representatives,representative_id',
            ]);

            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(6);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

            $query = SalesOrder::select(
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('order_status', '!=', 'Canceled'); // Exclude canceled orders

            // Apply segmentation filters (Rule 6.2)
            $joinedProducts = false;
            $joinedCustomers = false;

            if ($request->has('product_category_id') && !$joinedProducts) {
                $query->join('sales_order_items', 'sales_orders.order_id', '=', 'sales_order_items.order_id')
                    ->join('products', 'sales_order_items.product_id', '=', 'products.product_id');
                $joinedProducts = true;

                $query->where('products.product_category_id', $request->input('product_category_id'));
            }

            if (($request->has('region') || $request->has('customer_type')) && !$joinedCustomers) {
                $query->join('customers', 'sales_orders.customer_id', '=', 'customers.customer_id');
                $joinedCustomers = true;
            }

            if ($request->has('region')) {
                $query->where('customers.region', $request->input('region'));
            }
            if ($request->has('customer_type')) {
                $query->where('customers.customer_type', $request->input('customer_type'));
            }


            // Grouping by period (Rule 6.1)
            switch ($request->input('period')) {
                case 'daily':
                    $query->addSelect(DB::raw('DATE(order_date) as date_period'))
                          ->groupBy(DB::raw('DATE(order_date)'));
                    break;
                case 'weekly':
                    $query->addSelect(DB::raw('YEAR(order_date) as year'), DB::raw('WEEK(order_date) as week'))
                          ->groupBy(DB::raw('YEAR(order_date)'), DB::raw('WEEK(order_date)'));
                    break;
                case 'monthly':
                    $query->addSelect(DB::raw('DATE_FORMAT(order_date, \'%Y-%m\') as date_period'))
                          ->groupBy(DB::raw('DATE_FORMAT(order_date, \'%Y-%m\')'));
                    break;
                case 'annually':
                    $query->addSelect(DB::raw('YEAR(order_date) as date_period'))
                          ->groupBy(DB::raw('YEAR(order_date)'));
                    break;
            }

            $trends = $query->orderBy('date_period')->get();

            return response()->json($trends);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generating sales trends: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Track historical price changes per product/category alongside sales volume.
     * Rule 7.1: Tracks price changes.
     * Rule 7.2: Displays price trends alongside sales volume.
     * GET /api/v1/reports/price-trends?product_id=1&start_date=2024-01-01&end_date=2024-12-31
     */
    public function priceTrends(Request $request)
    {
        try {
            $this->validate($request, [
                'product_id' => 'required_without:product_category_id|exists:products,product_id',
                'product_category_id' => 'required_without:product_id|exists:product_categories,product_category_id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subYears(1);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

            $priceHistoryQuery = ProductPriceHistory::query()
                ->whereBetween('change_date', [$startDate, $endDate])
                ->orderBy('change_date');

            if ($request->has('product_id')) {
                $priceHistoryQuery->where('product_id', $request->input('product_id'));
                $productIds = [$request->input('product_id')];
            } else {
                $productIds = DB::table('products')
                    ->where('product_category_id', $request->input('product_category_id'))
                    ->pluck('product_id')
                    ->toArray();

                if (empty($productIds)) {
                    return response()->json([]);
                }

                $priceHistoryQuery->whereIn('product_id', $productIds);
            }

            $priceTrends = $priceHistoryQuery->get()->groupBy(function($entry) {
                return Carbon::parse($entry->change_date)->format('Y-m-d');
            })->map(function ($items, $date) {
                $latestPrice = $items->sortByDesc('change_date')->first();
                return [
                    'date' => $date,
                    'price' => $latestPrice->new_price,
                    'product_id' => $latestPrice->product_id,
                ];
            })->values()->sortBy('date');

            $salesVolume = SalesOrder::select(
                    DB::raw('DATE(order_date) as date'),
                    DB::raw('SUM(sales_order_items.quantity) as total_quantity_sold')
                )
                ->join('sales_order_items', 'sales_orders.order_id', '=', 'sales_order_items.order_id')
                ->whereIn('sales_order_items.product_id', $productIds)
                ->whereBetween('order_date', [$startDate, $endDate])
                ->where('order_status', '!=', 'Canceled')
                ->groupBy(DB::raw('DATE(order_date)'))
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $mergedData = $priceTrends->map(function ($pricePoint) use ($salesVolume) {
                $salesData = $salesVolume->get($pricePoint['date']);
                $pricePoint['sales_volume'] = $salesData ? (int) $salesData->total_quantity_sold : 0;
                return $pricePoint;
            });

            return response()->json($mergedData->values());

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generating price trends: ' . $e->getMessage()], 500);
        }
    }

    public function inventoryStatus(Request $request)
    {
        try {
            // Assuming you have an Inventory model or use Product model with stock quantity
            $inventoryData = DB::table('products')
                ->select('product_id', 'product_name', 'stock_quantity') // Adjust columns to your schema
                ->get();

            return response()->json($inventoryData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching inventory status: ' . $e->getMessage()], 500);
        }
    }
    // You can add predictive analytics (Rule 7.4) here.
    // This would typically involve more complex statistical/ML models,
    // which might be integrated as a separate service or a heavier library.
    // For a basic implementation, it could be a placeholder or a simple projection.
}