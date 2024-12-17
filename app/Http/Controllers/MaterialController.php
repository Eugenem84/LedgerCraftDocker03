<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\MaterialRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Driver\Selector;

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

        $products = DB::select('SELECT
                                        product_id, sale_price, quantity, name
                                      FROM order_product
                                      JOIN products on order_product.product_id = products.id
                                      WHERE order_id = ?',
                              [$orderId]);

        foreach ($products as $product) {
            $materials->push((object)[
               'name' => $product->name,
               'price' => $product->sale_price,
               'amount' => $product->quantity,
            ]);

        }

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
