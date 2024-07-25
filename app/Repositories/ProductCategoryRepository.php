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
        $storeCategory = new ProductCategory();
        $storeCategory->category_name = $newName;
        $storeCategory->specialization_id = $specializationId;
        $storeCategory->save();
    }

    public function delete($id)
    {
        $storeCategory = ProductCategory::find($id);
        if ($storeCategory){
            $storeCategory->products()->delete();
            $storeCategory->delete();
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
