<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\ProductCategoryRepository;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    protected $productCategoryRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository)
    {
        $this->storeCategoryRepository = $productCategoryRepository;
    }

    public function getBySpecialization($specializationId)
    {
        $productCategories = $this->productCategoryRepository->getBySpecialization($specializationId);
        if ($productCategories){
            return response()->json($productCategories);
        } else {
            return response()->json(['message' => 'Категорий не найдено']);
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
        $id = $request->input('categoryId');
        $result = $this->productCategoryRepository->delete($id);
        if ($result) {
            return response()->json(['message' => 'Категория удалена'], 200);
        } else {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }
    }

}
