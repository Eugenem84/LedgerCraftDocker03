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
            return response()->json($models);
    }
    public function addNew(Request $request)
    {
        try {
            $equipmentModel = $this->equipmentModelRepository->addNew(
                $request->input('name'),
                $request->input('specialization_id'),
            );

            return response()->json([
                'message' => 'Модель успешно добавлена',
                'model' => [
                    'id' => $equipmentModel->id,
                    'name' => $equipmentModel->name,
                    'specialization_id' => $equipmentModel->specialization_id
                ]
            ],201);
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
