<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    protected $table = 'loyalty_programs';
    protected $primaryKey = 'program_id';

    protected $fillable = [
        'program_name',
        'points_per_currency_unit',
        'redemption_rate',
        'min_redemption_points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function customerLoyaltyPoints()
    {
        return $this->hasMany(CustomerLoyaltyPoint::class, 'program_id', 'program_id');
    }
}