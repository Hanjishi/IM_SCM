<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $table = 'point_transactions';
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'customer_loyalty_id',
        'transaction_type',
        'points_change',
        'transaction_date',
        'order_id',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function customerLoyaltyPoint()
    {
        return $this->belongsTo(CustomerLoyaltyPoint::class, 'customer_loyalty_id', 'customer_loyalty_id');
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id', 'order_id');
    }
}