<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';
    protected $primaryKey = 'coupon_id';

    protected $fillable = [
        'promotion_id',
        'coupon_code',
        'coupon_type',
        'discount_value',
        'min_order_amount',
        'usage_limit',
        'times_used',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }

    public function appliedDiscounts()
    {
        return $this->hasMany(AppliedDiscount::class, 'coupon_id', 'coupon_id');
    }
}