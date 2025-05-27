<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'return_items'; // Make sure your table name is correct
    protected $primaryKey = 'return_item_id';

    protected $fillable = [
        'return_id',
        'product_id',
        'quantity',
        'reason'
    ];

    public function return()
    {
        return $this->belongsTo(Returns::class, 'return_id', 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}