<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderService;
use App\Models\Service;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\error;
use App\Repositories\SpecializationRepository;
use App\Repositories\ClientRepository;
use App\Repositories\ServiceRepository;

class OrderController extends Controller
{
    protected $orderRepository;
    protected $specializationRepository;
    protected $clientReposutory;
    protected $serviceRepository;

    public function __construct(OrderRepository $orderRepository,
                                SpecializationRepository $specializationRepository,
                                ClientRepository $clientRepository,
                                ServiceRepository $serviceRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->specializationRepository = $specializationRepository;
        $this->clientReposutory = $clientRepository;
        $this->serviceRepository = $serviceRepository;
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
        $servicesId = $this->orderRepository->getServicesId($orderId);
        foreach ($servicesId as $serviceId){
            $service = $this->serviceRepository->getService($serviceId);
            $services[] = $service;
        }
        return $services;

    }

    public function saveOrder(Request $request)
    {
        //$user = Auth::user();
        //$userId = $user->getAuthIdentifier();
        $data = $request->only(['clientId', 'specializationId', 'totalAmount', 'materials', 'comments']);
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
        return response('ордер удален');
    }

    public function updateOrder(Request $request)
    {
        $this->orderRepository->updateOrder(
            $request->input('id'),
            $request->input('client_id'),
            $request->input('specialization_id'),
            $request->input('materials'),
            $request->input('comments'),
            $request->input('services'),
            $request->input('total_amount')
        );
        return response()->json(['message' => 'Ордер успешно обновился']);
    }

}
