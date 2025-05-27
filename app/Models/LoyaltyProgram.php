<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    protected $table = 'loyalty_programs';
    protected $primaryKey = 'program_id';

    protected $fillable = [
        'program_name',
        'description',
        'points_multiplier',
        'minimum_purchase_amount',
        'points_expiry_days',
        'status'
    ];

    protected $casts = [
        'points_multiplier' => 'float',
        'minimum_purchase_amount' => 'float',
        'points_expiry_days' => 'integer',
        'status' => 'boolean',
    ];


    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class, 'program_id', 'program_id');
    }

    public function customers()
    {
        return $this->hasMany(CustomerLoyaltyPoint::class, 'program_id', 'program_id');
    }

}