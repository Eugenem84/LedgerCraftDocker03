<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentModel extends Model
{
    use HasFactory;

    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'model_id');
    }

    protected $fillable = [
        'specialization_id', 'name'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
