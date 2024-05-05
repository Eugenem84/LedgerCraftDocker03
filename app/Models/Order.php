<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'total_amount', 'user_id'];

    // связь с таблтцей специализации( один ко многим)
    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    //связь с таблицей клиентов (один ко многим)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    //связь с таблицей услуг (многие ко многим)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_service', 'order_id', 'service_id');
    }

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
