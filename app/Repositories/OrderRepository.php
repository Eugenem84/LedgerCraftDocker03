<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderRepository extends Controller
{
    public function switchPaidStatus($orderId)
    {
        return DB::update("
            UPDATE orders
            SET paid = NOT paid
            WHERE id = :id
        ", ['id' => $orderId]);
    }
    public function updatePaidStatus($orderId, $paidStatus)
    {
        $order = Order::find($orderId);
        if ($order){
            $order->paid = $paidStatus;
            $order->save();
            return $order;
        }
    }

    public function updateStatus($orderId, $status)
    {
        $order = Order::find($orderId);
        if ($order){
            $order->status = $status;
            $order->save();
            return $order;
        }
        return false;
    }
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
        $order->model_id = $data['modelId'];
        $order->total_amount = (int)$data['totalAmount'];
        $order->materials = $data['materials'];
        $order->comments = $data['comments'];
        $order->paid = $data['paid'];
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
                                $total_amount,
                                $paid,
                        )
    {
        $order = Order::find($orderId);
        $order->client_id = $clientId;
        $order->specialization_id = $specializationId;
        $order->total_amount = (int)$total_amount;
        $order->user_order_number = $user_order_number;
        //$order->materials = $materials;
        $order->comments = $comments;
        $order->paid = $paid;
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
