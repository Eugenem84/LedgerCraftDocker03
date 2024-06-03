<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\MaterialRepository;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    protected $materialRepository;

    public function __construct(MaterialRepository $materialRepository)
    {
        $this->materialRepository = $materialRepository;
    }
    public function getMaterialsByOrder(Request $request, $orderId)
    {
        $materials = $this->materialRepository->getMaterialsByOrderId($orderId);
        return response()->json($materials);
    }

    public function create(Request $request, $orderId)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'counter' => 'required|integer',
        ]);

        $data['order_id'] = $orderId;

        $material = $this->materialRepository->createMaterial($data, $orderId);
        return response()->json($material, 201);
    }
}
