<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at']; // Поле для мягкого удаления

    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    protected $fillable = [
        'specialization_id', 'name', 'phone'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
