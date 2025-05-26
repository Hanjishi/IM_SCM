<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'return_items';
    protected $primaryKey = 'return_item_id';

    protected $fillable = [
        'return_id',
        'product_id',
        'quantity',
        'unit_price_at_return',
    ];

    public function returns()
    {
        return $this->belongsTo(Returns::class, 'return_id', 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}