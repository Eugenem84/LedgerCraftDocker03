<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\Product;
use function Symfony\Component\String\s;

class ProductRepository extends Controller
{
    public function getByCategory($categoryId)
    {
        return Product::where('product_category_id', $categoryId)->get();
    }

    public function getProduct($id)
    {
        return Product::find($id);
    }

    public function addNew($name, $base_sale_price, $productCategoryId)
    {
        $product = new Product();
        $product->name = $name;
        $product->base_sale_price = $base_sale_price;
        $product->product_category_id = $productCategoryId;
        $product->save();
        return $product->id;
    }

    public function arrivalUpdate($productId, $baseSalePrice)
    {
        $product = Product::find($productId);
        $product->base_sale_price = $baseSalePrice;
        $product->save();
    }

    public function delete($id)
    {
        $service = Product::find($id);
        if ($service){
            $service->delete();
            return true;
        }
        return false;
    }
//
//    public function edit($id, $newName, $newPrice)
//    {
//        $service = Service::find($id);
//        if ($service){
//            $service->service = $newName;
//            $service->price = $newPrice;
//            $service->save();
//            return true;
//        } else {
//            return false;
//        }
//    }
}
