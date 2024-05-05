<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CategoryController;
use App\Models\Category;

class CategoryRepository extends Controller
{
    public function getBySpecialization($specializationId)
    {
        return Category::where('specialization_id', $specializationId)->get();
    }

    public function addNew($newName, $specializationId)
    {
        $category = new Category();
        $category->category_name = $newName;
        $category->specialization_id = $specializationId;
        $category->save();
    }

    public function delete($id)
    {
        $category = Category::find($id);
        if ($category){
            $category->services()->delete();
            $category->delete();
            return true;
        } else {
            return false;
        }
    }

    public function edit($id, $newName)
    {
        $category = Category::find($id);
        if ($category) {
            $category->category_name = $newName;
            $category->save();
            return true;
        } else {
            return false;
        }
    }
}
