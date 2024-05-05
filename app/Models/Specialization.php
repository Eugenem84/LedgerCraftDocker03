<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'specialization_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'specialization_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
