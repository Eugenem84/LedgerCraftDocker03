<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\Service;
use App\Http\Controllers\Controller;
use App\Models\Specialization;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use App\Repositories\ServiceRepository;

class ServiceController extends Controller
{
    protected $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function getSpecializations()
    {
        $specializations = Specialization::all();
        if ($specializations) {
            return response()->json($specializations);
        } else {
            return response()->json(['error' => 'специализаций не найдено']);
        }
    }
    //удаление ордера
    public function deleteOrder($orderId)
    {
        $order = Order::find($orderId);
        if (!$order){
            return response()->json(['error' => 'ордер не найден'], 404);
        }

        OrderService::where('order_id', $orderId)->delete();

        $order->delete();
        return response()->json(['message' => 'Ордер успешно удален'], 200);
    }

    //обновление ордера
    public function updateOrder(Request $request){
        $orderId = $request->input('id');
        $clientId = $request->input('client_id');
        $specializationId = $request->input('specialization_id');
        $materials = $request->input('materials');
        $comments = $request->input('comments');
        $serviceData = $request->input('services');
        $totalAmount = $request->input('total_amount');

        $order = Order::find($orderId);
        $order->client_id = $clientId;
        $order->total_amount = $totalAmount;
        $order->materials = $materials;
        $order->comments = $comments;
        $order->services()->sync($serviceData);
        $order->save();
        return response()->json(['message'=> 'Ордер успешно обновился', $totalAmount]);
    }

    //запрос на редактирование(отдает данные для редактора)
    public function editOrder($orderId){
        $order = Order::find($orderId);
        //получаем специализацию заказа
        $specialization = $order->specialization;
        //получаем клиентов специализации
        $clients = $specialization->clients;
        //получаем категории по специализации
        $categories = Category::where('specialization_id', $specialization->id)->get();
        if (!$order){
            return view(404,'order.not_found');
        } else {
            return view('service.edit_order', [
                'order' => $order,
                'clients' => $clients,
                'categories' => $categories,
            ]);
        }
    }

    public function saveOrder(Request $request)
    {
        //сохранение заказ-наряда
        $order = new Order();
        $order->client_id = $request->input('clientId');
        $order->specialization_id = $request->input('specializationId');
        $order->total_amount = (int)$request->input('totalAmount');
        $order->materials = $request->input('materials');
        $order->comments = $request->input('comments');
        $order->save();
        if ($request->has('servicesId') && is_array($request->input('servicesId'))) {
            $order->services()->attach($request->input('servicesId'));
        } else {
            return response()->json(['error' => 'ошибка записи']);
        }
        return response()->json(['message' => 'ордер сохранен']);
    }
    public function show()
    {
        $specializations = Specialization::all();
        $categories = Category::all();
        $services = Service::all();
        $clients = Client::all();

        return view('service.show', compact('categories', 'services', 'specializations', 'clients'));
    }
    public function getByCategory($categoryId){
        $services = $this->serviceRepository->getByCategory($categoryId);
        if ($services) {
            return response()->json($services);
        } else {
            return response()->json('нет сервисов в данной категории');
        }
    }

    public function getClientsBySpecialization($specializationId){
        $clients = Client::where('specialization_id', $specializationId)->get();
        if ($clients->isEmpty()){
            return response()->json(['message' => 'У данной специализации нет клиентов']);
        } else {
            return response()->json($clients);
        }
    }

    public function editServices() {
        $categories = Category::all();
        $services = Service::all();
        $specializations = Specialization::all();
        $clients = Client::all();
        return view('service.edit', compact('categories', 'services', 'specializations', 'clients'));
    }

    public function showHistoryOrders(){
        $orders = Order::all();
        return view('service.history', ['orders' => $orders]);
    }

    public function showStatistics(){
        return view('service.statistic');
    }

    public function addNew(Request $request){
        try {
            $name = $request->input('service');
            $price = $request->input('price');
            $categoryId = $request->input('category_id');
            $this->serviceRepository->addNew($name, $price, $categoryId);
            return response()->json(['message' => 'Сервис успешно добавлен']);
        } catch (\Exception){
            return response()->json(['error' => 'Ошибка добавления']);
        }
    }

    public function delete(Request $request)
    {
        $serviceId = $request->input('serviceId');

        $result = $this->serviceRepository->delete($serviceId);
        if ($result){
            return response()->json(['message' => 'Сурвис успешно удален'], 200);
        } else {
            return response()->json(['message' => 'Сервис не найден'], 404);
        }
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $newName = $request->input('service');
        $newPrice = $request->input('price');
        $result =  $this->serviceRepository->edit($id, $newName, $newPrice);
        if ($result) {
            return response()->json(['message' => 'Сервис успешно изменен'], 200);
        } else {
            return response()->json(['error' => 'Сервис не найден'], 404);
        }
    }

}
