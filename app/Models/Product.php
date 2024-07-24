<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'manufacturer',
        'product_number',
        'weight',
        'base_sale_price'
    ];

    public function salesProductsPrices()
    {
        return $this->hasMany(SalesProductsPrice::class);
    }

    public function ProductStocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function buyProductPrices()
    {
        return $this->hasMany(BuyProductPrice::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('sale_price', 'quantity')
                    ->using(OrderProduct::class)
                    ->withTimestamps();
    }
}
