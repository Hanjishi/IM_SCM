<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLoyaltyPoint extends Model
{
    protected $table = 'customer_loyalty_points';
    protected $primaryKey = 'customer_loyalty_id';

    protected $fillable = [
        'customer_id',
        'program_id',
        'current_points',
        'total_points_earned',
        'total_points_redeemed',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function program()
    {
        return $this->belongsTo(LoyaltyProgram::class, 'program_id', 'program_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'customer_loyalty_id', 'customer_loyalty_id');
    }
}