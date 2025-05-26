<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'promotion_id';

    protected $fillable = [
        'promotion_name',
        'promotion_type',
        'discount_value',
        'min_order_amount',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'promotion_id', 'promotion_id');
    }

    public function appliedDiscounts()
    {
        return $this->hasMany(AppliedDiscount::class, 'promotion_id', 'promotion_id');
    }
}