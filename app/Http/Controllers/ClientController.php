<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Repositories\ClientRepository;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    protected $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }
    public function getAll()
    {
        $clients = $this->clientRepository->getAll();
        if ($clients){
            return response()->json($clients);
        } else {
            return response()->json(['error' => 'ошибка загрузки клиентов']);
        }
    }

    public function getBySpecialization($specializationId)
    {
        $clients = $this->clientRepository->getBySpecialization($specializationId);
//        if ($clients->isEmpty()){
//            //return response()->json(['message' => 'У данной специализации нет клиентов']);
//        } else {
            return response()->json($clients);
//        }
    }
    public function addNew(Request $request)
    {
        try {
            $client = $this->clientRepository->addNew(
                $request->input('name'),
                $request->input('phone'),
                $request->input('specialization_id')
            );

            return response()->json([
                'message' => 'Клиент успешно добавлен!!!',
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone' => $client->phone,
                    'specialization_id' => $client->specialization_id
                ]
            ],201);

        } catch (\Exception){
            return response()->json(['error' => 'Ошибка при добавлении клиента']);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('clientId');
        $result = $this->clientRepository->delete($id);
        if ($result){
            return response()->json(['message' => 'клиент удален']);
        } else {
            return response()->json(['error' => 'клиент не найден']);
        }
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $newName = $request->input('name');
        $newPhone = $request->input('phone');
        $result = $this->clientRepository->edit($id, $newName, $newPhone);
        if ($result){
            return response()->json(['message' => 'Клиент успешно изменен'], 200);
        } else {
            return response()->json(['message' => 'Клиент не найден']);
        }
    }

}
