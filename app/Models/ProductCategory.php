<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_categories'; // Explicitly set table name
    protected $primaryKey = 'product_category_id'; // Explicitly set primary key

    protected $fillable = [
        'category_name',
        'category_description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id', 'product_category_id');
    }
}