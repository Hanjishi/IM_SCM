<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    protected $table = 'loyalty_points';
    protected $primaryKey = 'points_id';

    protected $fillable = [
        'customer_id',
        'program_id',
        'points_balance',
        'points_earned',
        'points_redeemed',
        'last_earned_date',
        'last_redeemed_date',
        'expiry_date',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'last_earned_date' => 'datetime',
        'last_redeemed_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function program()
    {
        return $this->belongsTo(LoyaltyProgram::class, 'program_id', 'program_id');
    }

    public function transactions()
    {
        return $this->hasMany(PointTransaction::class, 'points_id', 'points_id');
    }
}
