<?php

namespace App\Repositories;
use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;

class SpecializationRepository extends Controller
{
    public function getAll()
    {
        return Specialization::all();
    }

    public function getTest()
    {
        return 'test';
    }

    public function getName($id)
    {
        $specialization = Specialization::find($id);
        if ($specialization){
            $specializationName = $specialization->specializationName;
            if ($specializationName){
                return $specializationName;
            }
        }
        return 'специализация неизвестна';
    }

    public function edit($id, $newSpecializationName)
    {
        $specialization = Specialization::find($id);
        if ($specialization){
            $specialization->specializationName = $newSpecializationName;
            $specialization->save();
            return true;
        }
        return false;
    }

    public function addNew($specializationName, $userId)
    {
        $specialization = new Specialization();
        $specialization->specializationName = $specializationName;
        $specialization->user_id = $userId;
        $specialization->popularCounter = 1;
        $specialization->save();
    }

    public function delete($id)
    {
        $specialization = Specialization::find($id);
        if ($specialization){
            $specialization->clients()->delete();
            $specialization->categories()->each(function ($category){
                $category->services()->delete();
                $category->delete();
            });
            $specialization->delete();
            return true;
        }
        return false;
    }

}
