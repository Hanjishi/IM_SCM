<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\SalesRepresentativeController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\ReturnsController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\LoyaltyProgramController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // --- Customer Management ---
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

    // --- Product & Category Management ---
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::put('/products/{id}/stock', [ProductController::class, 'updateStock']);
    Route::get('/products/category/{categoryId}', [ProductController::class, 'getByCategory']);

    Route::get('/product-categories', [ProductCategoryController::class, 'index']);
    Route::post('/product-categories', [ProductCategoryController::class, 'store']);
    Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show']);
    Route::put('/product-categories/{id}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy']);

    // --- Sales Representatives ---
    Route::get('/sales-reps', [SalesRepresentativeController::class, 'index']);
    Route::post('/sales-reps', [SalesRepresentativeController::class, 'store']);
    Route::get('/sales-reps/{id}', [SalesRepresentativeController::class, 'show']);
    Route::put('/sales-reps/{id}', [SalesRepresentativeController::class, 'update']);
    Route::delete('/sales-reps/{id}', [SalesRepresentativeController::class, 'destroy']);

    // --- Quotation Management ---
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::put('/quotations/{id}', [QuotationController::class, 'update']);
    Route::post('/quotations/{id}/convert-to-order', [QuotationController::class, 'convertToOrder']);

    // --- Sales Order Processing ---
    Route::get('/orders', [SalesOrderController::class, 'index']);
    Route::post('/orders', [SalesOrderController::class, 'store']);
    Route::get('/orders/{id}', [SalesOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [SalesOrderController::class, 'updateStatus']);

    // --- Invoice & Payment Processing ---
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::post('/invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
    Route::get('/invoices/{id}/payments', [InvoiceController::class, 'getPayments']);
    Route::get('/invoices/overdue', [InvoiceController::class, 'getOverdueInvoices']);

    // --- Delivery Notes ---
    Route::get('/delivery-notes', [DeliveryNoteController::class, 'index']);
    Route::post('/delivery-notes', [DeliveryNoteController::class, 'store']);
    Route::get('/delivery-notes/{id}', [DeliveryNoteController::class, 'show']);
    Route::post('/orders/{id}/delivery-notes', [DeliveryNoteController::class, 'store']);

    // --- Returns & Complaints ---
    Route::get('/returns', [ReturnsController::class, 'index']);
    Route::post('/returns', [ReturnsController::class, 'store']);
    Route::get('/returns/{id}', [ReturnsController::class, 'show']);
    Route::put('/returns/{id}/status', [ReturnsController::class, 'updateStatus']);

    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
    Route::put('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);

    // --- Promotions & Loyalty Programs ---
    Route::get('/promotions', [PromotionController::class, 'index']);
    Route::post('/promotions', [PromotionController::class, 'store']);
    Route::get('/promotions/{id}', [PromotionController::class, 'show']);
    Route::put('/promotions/{id}', [PromotionController::class, 'update']);

    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::get('/coupons/{id}', [CouponController::class, 'show']);
    Route::put('/coupons/{id}', [CouponController::class, 'update']);

    Route::get('/loyalty-programs', [LoyaltyProgramController::class, 'index']);
    Route::post('/loyalty-programs', [LoyaltyProgramController::class, 'store']);
    Route::get('/loyalty-programs/{id}', [LoyaltyProgramController::class, 'show']);
    Route::put('/loyalty-programs/{id}', [LoyaltyProgramController::class, 'update']);
    Route::get('/customers/{id}/loyalty-points', [LoyaltyProgramController::class, 'getCustomerPoints']);

    // --- Reporting & Visualization Data ---
    Route::get('/reports/sales-trends', [ReportController::class, 'salesTrends']);
    Route::get('/reports/price-trends', [ReportController::class, 'priceTrends']);
    Route::get('/reports/customer-activity', [ReportController::class, 'customerActivity']);
    Route::get('/reports/inventory-status', [ReportController::class, 'inventoryStatus']);

    // --- Audit Logs (Internal/Admin only) ---
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
}); 