<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppliedDiscount extends Model
{
    protected $table = 'applied_discounts';
    protected $primaryKey = 'applied_discount_id';

    protected $fillable = [
        'order_id',
        'promotion_id',
        'coupon_id',
        'discount_amount',
    ];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id', 'order_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'coupon_id');
    }
}