<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\ProductStockRepository;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    protected $productStockRepository;

    public function __construct(ProductStockRepository $productStockRepository)
    {
        $this->productStockRepository = $productStockRepository;
    }

    public function getByProductCategory($categoryId)
    {
        $products = $this->productStockRepository->getByProductCategory($categoryId);
        if ($products){
            return response()->json($products);
        } else {
            return response()->json(['message' => 'товаров не найдено']);
        }
    }

    public function addNew(Request $request)
    {
        $newName = $request->input('name');
        $specializationId = $request->input('specialization_id');
        $this->productCategoryRepository->addNew($newName, $specializationId);
        return response()->json(['message' => $request]);
    }

    public function edit(Request $request){
        $id = $request->input('id');
        $newName = $request->input('name');
        $result = $this->productCategoryRepository->edit($id, $newName);
        if ($result){
            return response()->json(['message' => 'Категория успешно изменена'], 200);
        } else {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }
    }

    public function delete(Request $request){
        $id = $request->input('productCategoryId');
        $result = $this->productCategoryRepository->delete($id);
        if ($result) {
            return response()->json(['message' => 'Категория удалена'], 200);
        } else {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }
    }

}
