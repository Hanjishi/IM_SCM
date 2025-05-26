<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $table = 'customer_contacts';
    protected $primaryKey = 'contact_id';

    protected $fillable = [
        'customer_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_role',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}