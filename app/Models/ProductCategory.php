<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }
}
