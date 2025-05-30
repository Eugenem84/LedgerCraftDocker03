<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function equipmentModels(){
        return $this->belongsTo(EquipmentModel::class);
    }

    //связь с таблицей услуг (многие ко многим)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_service', 'order_id', 'service_id')
            ->withPivot('sale_price', 'quantity');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
                    ->withPivot('sale_price','quantity')
                    ->withTimestamps();
    }
}
