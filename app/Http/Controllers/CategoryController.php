<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getBySpecialization($specializationId)
    {
        $categories = $this->categoryRepository->getBySpecialization($specializationId);
        if ($categories){
            return response()->json($categories);
        } else {
            return response()->json(['message' => 'Категорий не найдено']);
        }
    }

    public function addNew(Request $request)
    {
        $newName = $request->input('category_name');
        $specializationId = $request->input('specialization_id');
        $this->categoryRepository->addNew($newName, $specializationId);
        return response()->json(['message' => $request]);
    }

    public function edit(Request $request){
        $id = $request->input('id');
        $newName = $request->input('category_name');
        $result = $this->categoryRepository->edit($id, $newName);
        if ($result){
            return response()->json(['message' => 'Категорияэ успешно изменена'], 200);
        } else {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }
    }

    public function delete(Request $request){
        $id = $request->input('categoryId');
        $result = $this->categoryRepository->delete($id);
        if ($result) {
            return response()->json(['message' => 'Категория удалена'], 200);
        } else {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }
    }

}
