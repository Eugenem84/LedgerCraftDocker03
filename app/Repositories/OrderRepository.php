<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderService;
use Illuminate\Support\Facades\Auth;

class OrderRepository extends Controller
{
    public function getAll()
    {
        return Order::all();
    }

    public function getByUser($userId)
    {
        return Order::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    public function getDetails($id)
    {
        return Order::find($id);
    }

    public function getServicesId($id)
    {
        $orderServices = OrderService::where('order_id', $id)->pluck('service_id');
        return $orderServices;
    }

    public function saveOrder(array $data)
    {
        //сохранение заказ-наряда
        $user = Auth::user();
        $userId = optional($user)->getAuthIdentifier();
        $order = new Order();
        $order->client_id = $data['clientId'];
        $order->user_order_number = $data['userOrderNumber'];
        $order->specialization_id = $data['specializationId'];
        $order->status = $data['status'];
        $order->user_id = $userId;
        $order->total_amount = (int)$data['totalAmount'];
        $order->materials = $data['materials'];
        $order->comments = $data['comments'];
        $order->save();

        if (isset($data['servicesId']) && is_array($data['servicesId'])) {
            $order->services()->attach($data['servicesId']);
        }

        if (isset($data['addedMaterials']) && is_array($data['addedMaterials'])) {
            foreach ($data['addedMaterials'] as $materialData) {
                $material = new Material();
                $material->order_id = $order->id;
                $material->name = $materialData['name'];
                $material->price = $materialData['price'];
                $material->amount = $materialData['counter'];
                $material->save();
            }
        }
        return $order;
    }

    public function deleteOrder($id)
    {
        $order = Order::find($id);
        OrderService::where('order_id', $id)->delete();
        $order->delete();
    }

    public function updateOrder($orderId,
                                $clientId,
                                $specializationId,
                                $user_order_number,
                                $materials,
                                $comments,
                                $servicesData,
                                $total_amount)
    {
        $order = Order::find($orderId);
        $order->client_id = $clientId;
        $order->specialization_id = $specializationId;
        $order->total_amount = (int)$total_amount;
        $order->user_order_number = $user_order_number;
        //$order->materials = $materials;
        $order->comments = $comments;
        $order->services()->sync($servicesData);
        $order->save();

        if (isset($materials) && is_array($materials)) {
            // Удаляем существующие материалы
            Material::where('order_id', $order->id)->delete();

            // Добавляем новые материалы
            foreach ($materials as $materialData) {
                $material = new Material();
                $material->order_id = $order->id;
                $material->name = $materialData['name'];
                $material->price = $materialData['price'];
                $material->amount = $materialData['amount'];
                $material->save();
            }
        }
    }
}
