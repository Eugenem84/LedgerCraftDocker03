<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    use HasFactory;

    protected $table = 'order_service';
    protected $primaryKey = ['order_id', 'service_id'];
    public $incrementing = false;

    protected $fillable = ['order_id', 'service_is'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function  service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
