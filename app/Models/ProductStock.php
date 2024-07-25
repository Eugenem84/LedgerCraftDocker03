<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'supplier',
    ];

    public function product()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
