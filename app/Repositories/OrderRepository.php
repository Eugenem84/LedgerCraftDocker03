<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderService;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\table;

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

        if (isset($data['addedProducts']) && is_array($data['addedProducts'])) {
            $productData = [];

            foreach ($data['addedProducts'] as $addedProduct) {
                $productStock = ProductStock::where('product_id', $addedProduct['productId'])->first();
                if ($productStock && $productStock->quantity >= $addedProduct['counter']) {
                    $productStock->quantity -= $addedProduct['counter'];
                    $productStock->save();

                    // данные для расходного ордера order_product
                    $productData[$addedProduct['productId']] = [
                        'sale_price' => $addedProduct['price'],
                        'quantity' => $addedProduct['counter']
                    ];
                } else {
                    throw new \Exception('Product stock not found');
                }
            }
            $order->products()->attach($productData);
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
                                $modelId,
                                $specializationId,
                                $user_order_number,
                                $materials,
                                $products,
                                $comments,
                                $servicesData,
                                $total_amount,
                                $paid,
                        )
    {
        $order = Order::find($orderId);
        $order->client_id = $clientId;
        $order->model_id = $modelId;
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

        if (isset($products) && is_array($products)) {
            OrderProduct::where('order_id', $order->id)->delete();
//            $order->products()->attach($products);

            foreach ($products as $productData) {
                $product = new OrderProduct();
                $product->order_id = $order->id;
                $product->product_id = $productData['product_id'];
                $product->sale_price = $productData['price'];
                $product->quantity = $productData['amount'];
                $product->save();
            }
        }

    }
}
