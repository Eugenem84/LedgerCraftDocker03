<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductStockController;
use App\Models\Category;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;

class ProductStockRepository extends Controller
{
    public function getByProductCategory($categoryId)
    {
        return DB::select("
            SELECT * FROM product_stocks
            JOIN products on product_stocks.product_id = products.id
            WHERE product_categories_id = :product_categories_id
        ", ['product_categories_id' => $categoryId]);
    }

    public function addNew($productId, $categoryCategoryId)
    {
        $productStock = new ProductStock();
        $productStock->product_id = $productId;
        $productStock->product_categories_id = $categoryCategoryId;
        $productStock->quantity = 0;
        $productStock->save();
        return $productStock;
    }

    public function arrival($productId, $arrivalQuantity)
    {
        $productStock = ProductStock::where($productId);
        $productStock->quantity += $arrivalQuantity;
        $productStock->save();
    }
//    public function addNew($newName, $specializationId)
//    {
//        $productCategory = new ProductCategory();
//        $productCategory->name = $newName;
//        $productCategory->specialization_id = $specializationId;
//        $productCategory->save();
//        return $productCategory;
//    }
//
//    public function delete($id)
//    {
//        $productCategory = ProductCategory::find($id);
//        if ($productCategory){
//            //$productCategory->products()->delete();
//            $productCategory->delete();
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    public function edit($id, $newName)
//    {
//        $category = ProductCategory::find($id);
//        if ($category) {
//            $category->name = $newName;
//            $category->save();
//            return true;
//        } else {
//            return false;
//        }
//    }
}
