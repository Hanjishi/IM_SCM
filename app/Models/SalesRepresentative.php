<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesRepresentative extends Model
{
    protected $table = 'sales_representatives';
    protected $primaryKey = 'sales_rep_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'region',
        'commission_rate',
        'is_active'
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'sales_rep_id', 'sales_rep_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'sales_rep_id', 'sales_rep_id');
    }
}