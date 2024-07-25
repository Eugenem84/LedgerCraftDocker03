<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CategoryController;
use App\Models\Category;
use App\Models\ProductCategory;

class ProductCategoryRepository extends Controller
{
    public function getBySpecialization($specializationId)
    {
        return ProductCategory::where('specialization_id', $specializationId)->get();
    }

    public function addNew($newName, $specializationId)
    {
        $productCategory = new ProductCategory();
        $productCategory->name = $newName;
        $productCategory->specialization_id = $specializationId;
        $productCategory->save();
        return $productCategory;
    }

    public function delete($id)
    {
        $productCategory = ProductCategory::find($id);
        if ($productCategory){
            //$productCategory->products()->delete();
            $productCategory->delete();
            return true;
        } else {
            return false;
        }
    }

    public function edit($id, $newName)
    {
        $category = ProductCategory::find($id);
        if ($category) {
            $category->name = $newName;
            $category->save();
            return true;
        } else {
            return false;
        }
    }
}
