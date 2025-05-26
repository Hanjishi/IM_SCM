<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    protected $table = 'returns'; // 'returns' is a reserved keyword in some SQL, hence explicitly setting table name
    protected $primaryKey = 'return_id';

    protected $fillable = [
        'order_id',
        'customer_id',
        'return_date',
        'reason_for_return',
        'product_condition',
        'resolution_status',
        'refund_amount',
        'refund_method',
        'restocking_fee',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id', 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id', 'return_id');
    }
}