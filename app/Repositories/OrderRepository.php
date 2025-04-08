<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderService;
use App\Models\ProductStock;
use App\Models\Service;
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
        ", ['id' => $orderId]
        );
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

    public function getBySpecialization($id)
    {
        return DB::select("
          SELECT orders.*, clients.name AS client_name, equipment_models.name AS model_name
          FROM orders
          JOIN clients ON orders.client_id = clients.id
          JOIN equipment_models ON orders.model_id = equipment_models.id
          WHERE orders.specialization_id = :specialization_id
        ", ['specialization_id' => $id]
        );
        //return Order::where('specialization_id', $id)->orderBy('created_at', 'desc')->get();
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
        $order->comments = $data['comments'];
        $order->paid = $data['paid'];
        $order->save();


        if (isset($data['services']) && is_array($data['services'])) {
            $serviceData = [];

            foreach ($data['services'] as $service) {
                $serviceId = $service['id'];
                $price = $service['price'];
                $quantity = $service['quantity'];

                $serviceData[$serviceId] = [
                    'sale_price' => $price,
                    'quantity' => $quantity,
                ];
            }

            $order->services()->attach($serviceData);
        }


        if (isset($data['addedProducts']) && is_array($data['addedProducts'])) {
            $productData = [];

            foreach ($data['addedProducts'] as $addedProduct) {
                $productStock = ProductStock::where('product_id', $addedProduct['product_id'])->first();
                if ($productStock && $productStock->quantity >= $addedProduct['amount']) {
                    $productStock->quantity -= $addedProduct['amount'];
                    $productStock->save();

                    // данные для расходного ордера order_product
                    $productData[$addedProduct['product_id']] = [
                        'sale_price' => $addedProduct['price'],
                        'quantity' => $addedProduct['amount']
                    ];
                } else {
                    throw new \Exception('недотаточен остаток по товару');
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
                $material->amount = $materialData['amount'];
                $material->save();
            }
        }
        return $order;
    }

    public function deleteOrder($id)
    {
        $order = Order::find($id);
        if (!$order) {
            throw new \Exception('Order not found');
        }

        foreach ($order->products as $product) {
            $orderedQuantity = $product->pivot->quantity;
            $productStock = ProductStock::where('product_id', $product->id)->first();
            if($productStock) {
                $productStock->quantity += $orderedQuantity;
                $productStock->save();
            }
        }
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
        //$order->services()->sync($servicesData);
        $order->save();

        if (isset($servicesData) && is_array($servicesData)) {
            $serviceData = [];
            foreach ($servicesData as $service) {
                $serviceId = $service['id']; // получаем id услуги
                $price = $service['price'];  // получаем цену из данных
                $quantity = $service['quantity'] ?? 1; // получаем количество, если оно есть (по умолчанию 1)

                $serviceData[$serviceId] = [
                    'sale_price' => $price, // используем цену, которая пришла с фронта
                    'quantity' => $quantity  // и количество, которое пришло с фронта
                ];
            }
            $order->services()->sync($serviceData); // синхронизируем связи с учетом новых данных
        }


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
