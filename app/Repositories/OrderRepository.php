<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
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
        return Order::where('user_id', $userId)->get();
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
        $order->specialization_id = $data['specializationId'];
        $order->user_id = $userId;
        $order->total_amount = (int)$data['totalAmount'];
        $order->materials = $data['materials'];
        $order->comments = $data['comments'];
        $order->save();

        if (isset($data['servicesId']) && is_array($data['servicesId'])) {
            $order->services()->attach($data['servicesId']);
            return $order;
        } else {
            return null;
        }
    }

    public function deleteOrder($id)
    {
        $order = Order::find($id);
        OrderService::where('order_id', $id)->delete();
        $order->delete();
    }

    public function updateOrder($orderId, $clientId, $specializationId, $materials, $comments, $servicesData, $totalAmount)
    {
        $order = Order::find($orderId);
        $order->client_id = $clientId;
        $order->specialization_id = $specializationId;
        $order->total_amount = $totalAmount;
        $order->materials = $materials;
        $order->comments = $comments;
        $order->services()->sync($servicesData);
        $order->save();
    }
}
