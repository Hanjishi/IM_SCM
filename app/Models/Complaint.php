<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaints';
    protected $primaryKey = 'complaint_id';

    protected $fillable = [
        'customer_id',
        'order_id',
        'product_id',
        'complaint_date',
        'complaint_type',
        'description',
        'priority',           
        'resolution_status',  
        'resolution_details', 
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
