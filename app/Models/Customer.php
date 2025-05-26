<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone_number',
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_state',
        'billing_zip_code',
        'billing_country',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_zip_code',
        'shipping_country',
        'credit_limit',
        'customer_type',
        'industry',
        'region',
    ];

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class, 'customer_id', 'customer_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id', 'customer_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'customer_id');
    }

    public function returns()
    {
        return $this->hasMany(Returns::class, 'customer_id', 'customer_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'customer_id', 'customer_id');
    }

    public function loyaltyPoints()
    {
        return $this->hasOne(CustomerLoyaltyPoint::class, 'customer_id', 'customer_id');
    }
}