<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'customer_id',
        'sales_rep_id',
        'quotation_id',
        'order_date',
        'delivery_date',
        'order_status',
        'total_amount',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_zip_code',
        'shipping_country',
        'shipping_cost',
        'tax_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function salesRepresentative()
    {
        return $this->belongsTo(SalesRepresentative::class, 'sales_rep_id', 'sales_rep_id');
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'quotation_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'order_id', 'order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'order_id');
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class, 'order_id', 'order_id');
    }

    public function returns()
    {
        return $this->hasMany(Returns::class, 'order_id', 'order_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'order_id', 'order_id');
    }

    public function appliedDiscounts()
    {
        return $this->hasMany(AppliedDiscount::class, 'order_id', 'order_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'order_id', 'order_id');
    }
}