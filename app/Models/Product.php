<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_name',
        'product_description',
        'sku',
        'unit_price',
        'stock_quantity',
        'product_category_id',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'product_category_id');
    }

    public function priceHistory()
    {
        return $this->hasMany(ProductPriceHistory::class, 'product_id', 'product_id');
    }
}