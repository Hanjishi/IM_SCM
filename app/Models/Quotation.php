<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotations';
    protected $primaryKey = 'quotation_id';
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'sales_rep_id',
        'quotation_date',
        'valid_until',
        'quotation_status',
        'total_amount',
        'terms_conditions',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function salesRepresentative()
    {
        return $this->belongsTo(SalesRepresentative::class, 'sales_rep_id', 'sales_rep_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id', 'quotation_id');
    }

    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class, 'quotation_id', 'quotation_id');
    }
}