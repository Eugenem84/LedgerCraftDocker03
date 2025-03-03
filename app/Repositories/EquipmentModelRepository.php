<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\EquipmentModel;

class EquipmentModelRepository extends Controller
{
    public function getAll()
    {
        return EquipmentModel::all();
    }

    public function getBySpecialization($specializationId)
    {
        return EquipmentModel::where('specialization_id', $specializationId)->get();
    }

    public function getName($id)
    {
        $model = EquipmentModel::find($id);
        if ($model){
            $modelName = $model->name;
            if ($modelName){
                return $modelName;
            }
        }
        return 'модель неизвестна';
    }

    public function addNew($name, $specializationId )
    {
        $model = new EquipmentModel();
        $model->name = $name;
        $model->specialization_id = $specializationId;
        $model->save();
        return $model->fresh();
    }

    public function delete($id)
    {
        $model = EquipmentModel::find($id);
        if ($model){
            $model->delete();
            return true;
        } else {
            return false;
        }
    }

    public function edit($id, $newName)
    {
        $model = EquipmentModel::find($id);
        if ($model){
            $model->name = $newName;
            $model->save();
            return true;
        } else {
            return false;
        }
    }

}
