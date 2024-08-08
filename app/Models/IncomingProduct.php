<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'supplier',
        'quantity',
        'by_price',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
