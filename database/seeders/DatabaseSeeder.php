<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\LoyaltyProgram;
use App\Models\CustomerLoyaltyPoint;
use App\Models\PointTransaction;
use App\Models\Promotion;
use App\Models\Coupon;
use App\Models\AppliedDiscount;
use App\Models\ProductPriceHistory;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\DeliveryNote;
use App\Models\Returns;
use App\Models\ReturnItem;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\SalesRepresentative;
use App\Models\AuditLog;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create product categories
        $categories = [
            ['category_name' => 'Electronics', 'category_description' => 'Electronic devices and accessories'],
            ['category_name' => 'Clothing', 'category_description' => 'Apparel and fashion items'],
            ['category_name' => 'Home & Kitchen', 'category_description' => 'Home appliances and kitchenware'],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }

        // Create products
        $products = [
            [
                'product_name' => 'Smartphone X',
                'product_description' => 'Latest smartphone with advanced features',
                'sku' => 'PHN-001',
                'unit_price' => 999.99,
                'stock_quantity' => 100,
                'product_category_id' => 1,
            ],
            [
                'product_name' => 'Laptop Pro',
                'product_description' => 'High-performance laptop for professionals',
                'sku' => 'LPT-001',
                'unit_price' => 1499.99,
                'stock_quantity' => 50,
                'product_category_id' => 1,
            ],
            [
                'product_name' => 'Designer T-Shirt',
                'product_description' => 'Premium cotton t-shirt',
                'sku' => 'TSH-001',
                'unit_price' => 29.99,
                'stock_quantity' => 200,
                'product_category_id' => 2,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create loyalty program
        $loyaltyProgram = LoyaltyProgram::create([
            'program_name' => 'Standard Rewards',
            'points_per_currency_unit' => 1,
            'redemption_rate' => 0.01,
            'min_redemption_points' => 1000,
            'is_active' => true,
        ]);

        // Create sales representatives
        $salesReps = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'territory' => 'North Region',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '0987654321',
                'territory' => 'South Region',
            ],
        ];

        foreach ($salesReps as $rep) {
            SalesRepresentative::create($rep);
        }

        // Create customers with contacts
        $customers = [
            [
                'name' => 'ABC Corporation',
                'email' => 'contact@abccorp.com',
                'phone' => '555-0101',
                'address' => '123 Business St, City',
                'customer_type' => 'corporate',
                'credit_limit' => 50000.00,
                'payment_terms' => 'Net 30',
                'sales_representative_id' => 1,
                'contacts' => [
                    [
                        'contact_name' => 'Robert Johnson',
                        'contact_position' => 'Procurement Manager',
                        'contact_email' => 'robert@abccorp.com',
                        'contact_phone' => '555-0102',
                    ],
                ],
            ],
            [
                'name' => 'XYZ Ltd',
                'email' => 'info@xyzltd.com',
                'phone' => '555-0201',
                'address' => '456 Enterprise Ave, Town',
                'customer_type' => 'corporate',
                'credit_limit' => 30000.00,
                'payment_terms' => 'Net 15',
                'sales_representative_id' => 2,
                'contacts' => [
                    [
                        'contact_name' => 'Sarah Williams',
                        'contact_position' => 'Purchasing Director',
                        'contact_email' => 'sarah@xyzltd.com',
                        'contact_phone' => '555-0202',
                    ],
                ],
            ],
        ];

        foreach ($customers as $customerData) {
            $contacts = $customerData['contacts'];
            unset($customerData['contacts']);
            
            $customer = Customer::create($customerData);
            
            foreach ($contacts as $contact) {
                $contact['customer_id'] = $customer->customer_id;
                CustomerContact::create($contact);
            }

            // Create loyalty points for customer
            CustomerLoyaltyPoint::create([
                'customer_id' => $customer->customer_id,
                'program_id' => $loyaltyProgram->program_id,
                'points_balance' => 0,
            ]);
        }

        // Create a sample quotation
        $quotation = Quotation::create([
            'customer_id' => 1,
            'quotation_date' => now(),
            'valid_until' => now()->addDays(30),
            'total_amount' => 2999.97,
            'status' => 'pending',
            'notes' => 'Sample quotation for electronics',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->quotation_id,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 999.99,
            'subtotal' => 1999.98,
        ]);

        // Create a sample sales order
        $salesOrder = SalesOrder::create([
            'customer_id' => 1,
            'order_date' => now(),
            'total_amount' => 2999.97,
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => '123 Business St, City',
            'notes' => 'Sample order for electronics',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->sales_order_id,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 999.99,
            'subtotal' => 1999.98,
        ]);

        // Create a sample invoice
        $invoice = Invoice::create([
            'sales_order_id' => $salesOrder->sales_order_id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 2999.97,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Create a sample payment
        Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now(),
            'amount' => 2999.97,
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'reference_number' => 'PAY-001',
        ]);

        // Create a sample delivery note
        DeliveryNote::create([
            'sales_order_id' => $salesOrder->sales_order_id,
            'delivery_date' => now(),
            'status' => 'pending',
            'notes' => 'Handle with care',
        ]);

        // Create a sample promotion
        $promotion = Promotion::create([
            'promotion_name' => 'Summer Sale',
            'description' => '20% off on all electronics',
            'start_date' => now(),
            'end_date' => now()->addMonths(1),
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        // Create a sample coupon
        Coupon::create([
            'promotion_id' => $promotion->promotion_id,
            'code' => 'SUMMER20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'min_purchase_amount' => 100,
            'max_discount_amount' => 500,
            'start_date' => now(),
            'end_date' => now()->addMonths(1),
            'is_active' => true,
        ]);

        // Create a sample audit log
        AuditLog::create([
            'user_id' => 1,
            'action' => 'created',
            'entity_type' => 'customer',
            'entity_id' => 1,
            'details' => 'Created new customer ABC Corporation',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
