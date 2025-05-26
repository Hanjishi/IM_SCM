<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $table = 'delivery_notes';
    protected $primaryKey = 'delivery_note_id';

    protected $fillable = [
        'order_id',
        'delivery_date',
        'shipper',
        'tracking_number',
        'delivery_status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id', 'order_id');
    }
}