<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public  function specialization(){
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }
}
