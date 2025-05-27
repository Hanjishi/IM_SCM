<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    protected $table = 'product_price_history'; // or the correct table name
    protected $primaryKey = 'price_history_id'; // or your actual primary key
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'old_price',
        'new_price',
        'change_date',
        // add other columns if needed
    ];
}
