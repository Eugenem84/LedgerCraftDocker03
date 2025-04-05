<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderService;
use App\Models\Service;
use App\Models\Specialization;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\error;
use App\Repositories\SpecializationRepository;
use App\Repositories\ClientRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\EquipmentModelRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;



class OrderController extends Controller
{
    protected $orderRepository;
    protected $specializationRepository;
    protected $clientReposutory;
    protected $serviceRepository;

    protected $equipmentModelRepository;

    public function __construct(OrderRepository $orderRepository,
                                SpecializationRepository $specializationRepository,
                                ClientRepository $clientRepository,
                                ServiceRepository $serviceRepository,
                                EquipmentModelRepository $equipmentModelRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->specializationRepository = $specializationRepository;
        $this->clientReposutory = $clientRepository;
        $this->serviceRepository = $serviceRepository;
        $this->equipmentModelRepository = $equipmentModelRepository;
    }

    public function getBySpecialization(Request $request, $id)
    {
        $orders = $this->orderRepository->getBySpecialization($id);
        if ($orders){
            return response()->json($orders);
        } else {
            return response()->json(['message' => 'ордеров нет']);
        }
    }

    public function switchPaidStatus(Request $request, $id)
    {
        $order = $this->orderRepository->switchPaidStatus($id);
        if ($order){
            return response()->json(['message' => 'статус оплаты изменен']);
        }
        return response(['message' => 'невозможно изменить статус оплаты']);
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $order = $this->orderRepository->updateStatus($id, $request->status);
        if($order){
            return response()->json(['message' => 'Order status updated successfully.'], 200);
        }
        return response()->json(['message' => 'Unable to update order status.'], 500);
    }

    public function updatePadeStatus(Request $request, $id)
    {
        $order = $this->orderRepository->updatePaidStatus($id, $request->paid);
        if ($order){
            return response()->json(['message' => 'статус оплаты изменен'], 200);
        }
        return response()->json(['message' => 'ошибка обновления статуса'], 500);
    }

    public function getAll()
    {
        $orders = $this->orderRepository->getAll();
        foreach ($orders as $order) {
            $clientId = $order->client_id;
            $specializationId = $order->specialization_id;

            $clientName = $this->clientReposutory->getName($clientId);
            $specializationName = $this->specializationRepository->getName($specializationId);

            if($specializationName){
                $order->specialization_name = $specializationName;
            } else {
                $order->specialization_name = 'no name';
            }

            if ($clientName){
                $order->client_name = $clientName;
            } else {
                $order->specialization_name = 'no name';
            }

        }
        if ($orders) {
            return response()->json($orders);
        } else {
            return response()->json(['error' => 'ордеров нет']);
        }
    }

    public function getByUser()
    {
        $user = Auth::user();
        $userId  = $user->getAuthIdentifier();
        $orders = $this->orderRepository->getByUser($userId);
        foreach ($orders as $order) {
            $clientId = $order->client_id;
            $specializationId = $order->specialization_id;
            $modelId = $order->model_id;

            //$order->status = json_decode($order->status, true);

            $clientName = $this->clientReposutory->getName($clientId);
            $clientPhone = $this->clientReposutory->getPhone($clientId);
            $specializationName = $this->specializationRepository->getName($specializationId);
            $modelName = $this->equipmentModelRepository->getName($modelId);

            if($specializationName){
                $order->specialization_name = $specializationName;
            } else {
                $order->specialization_name = 'no name';
            }

            if ($clientName){
                $order->client_name = $clientName;
            } else {
                $order->client_name = 'no name';
            }

            if ($clientPhone){
                $order->client_phone = $clientPhone;
            }else{
                $order->client_phone = 'no phone';
            }

            if($modelName){
                $order->model_name = $modelName;
            }else{
                $order->model_name = 'no name';
            }

        }
        if ($orders) {
            return response()->json($orders);
        } else {
            return response()->json(['error' => 'ордеров нет']);
        }
    }

    public function getOrdersByUser($id)
    {
        $orders = $this->orderRepository->getByUser($id);
        return \response()->json($orders);
    }

    public function getDetails($id){
        $order = $this->orderRepository->getDetails($id);
        if (!$order){
            return response()->json(['message' => 'ордер'. $id . ' не найден']);
        } else {
            return response()->json($order);
        }
    }

    public function getServices($orderId)
    {
       $services = [];

       $orderServices = DB::table('order_service')
           ->where('order_id', $orderId)
           ->join('services', 'order_service.service_id', '=', 'services.id')
           ->select('services.id as id', 'services.service as service', 'order_service.sale_price')
           ->get();
       foreach ($orderServices as $orderService) {
           $services[] = [
             'id' => $orderService->id,
             'service' => $orderService->service,
             'price' => $orderService->sale_price,
           ];
       }
       return $services;
    }

    public function saveOrder(Request $request)
    {
        $data = $request->only([
            'clientId',
            'userOrderNumber',
            'specializationId',
            'status',
            'totalAmount',
            'modelId' ,
            'comments',
            'addedMaterials',
            'addedProducts',
            'paid'
        ]);
        $data['servicesId'] = $request->input('servicesId');
        $order = $this->orderRepository->saveOrder($data);
        if ($order){
            return response()->json(['message' => 'ордер сохранен']);
        } else {
            return response()->json(['error' => 'ошибка записи ордера']);
        }
    }
    public function deleteOrder($id)
    {
        $this->orderRepository->deleteOrder($id);
        return response('ордер удален', 200);
    }

    public function updateOrder(Request $request)
    {
        $this->orderRepository->updateOrder(
            $request->input('id'),
            $request->input('client_id'),
            $request->input('model_id'),
            $request->input('specialization_id'),
            $request->input('user_order_number'),
            $request->input('materials'),
            $request->input('products'),
            $request->input('comments'),
            $request->input('services'),
            $request->input('total_amount'),
            $request->input('paid')
        );
        return response()->json(['message' => 'Ордер успешно обновился']);
    }

// Показ отчёта
    public function showReport(Order $order)
    {
        return view('order-report', [
            'order' => $order->load(['client', 'services', 'material', 'products'])
        ]);
    }

    public function generateShareLink(Order $order)
    {
        return response()->json([
            'url' => route('order-report', ['order' => $order->id]),
        ]);
    }

}


