<?php

namespace App\Repositories;
use App\Models\Material;
use App\Models\Order;


class MaterialRepository
{
    public function getMaterialsByOrderId($orderId){

        if (is_null($orderId)) {
            throw new \InvalidArgumentException("Order id cannot be null");
        }

        return Material::where('order_id', $orderId)->get();
    }

    public function getMaterialById($id){
        //
    }

    public function createMaterial($orderId, array $data){
      return Material::create($data);
    }

    public function updateMaterial($data){
        //
    }

    public function deleteMaterial($orderId, $materialId)
    {
        $order = Order::findOrFail($orderId);
        $material = $order->materials()->findOrFail($materialId);
        return $material->delete();
    }
}
