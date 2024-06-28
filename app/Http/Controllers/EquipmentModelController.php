<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Repositories\ClientRepository;
use App\Repositories\EquipmentModelRepository;
use Illuminate\Http\Request;

class EquipmentModelController extends Controller
{
    protected $ModelRepository;

    public function __construct(EquipmentModelRepository $equipmentModelRepository)
    {
        $this->equipmentModelRepository = $equipmentModelRepository;
    }

    public function getBySpecialization($specializationId)
    {
        $models = $this->equipmentModelRepository->getBySpecialization($specializationId);
//        if ($clients->isEmpty()){
//            //return response()->json(['message' => 'У данной специализации нет клиентов']);
//        } else {
            return response()->json($models);
//        }
    }
    public function addNew(Request $request)
    {
        try {
            $name = $request->input('name');
            $specializationId = $request->input('specialization_id');
            $this->equipmentModelRepository->addNew($name, $specializationId);
            return response()->json(['message' => 'Модель успешно добавлена']);
        } catch (\Exception){
            return response()->json(['error' => 'Ошибка при добавлении модели']);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('clientId');
        $result = $this->equipmentModelRepository->delete($id);
        if ($result){
            return response()->json(['message' => 'модель удалена']);
        } else {
            return response()->json(['error' => 'модель не найдена']);
        }
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $newName = $request->input('name');
        $result = $this->equipmentModelRepository->edit($id, $newName);
        if ($result){
            return response()->json(['message' => 'Модель успешно изменена'], 200);
        } else {
            return response()->json(['message' => 'Модель не найдена']);
        }
    }

}
