<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\IncomingProduct;
use App\Models\Product;
use function Symfony\Component\String\s;

class IncomingProductRepository extends Controller
{
    protected $incoming;

    public function __construct(IncomingProduct $incomingProduct)
    {
        $this->incoming = $incomingProduct;
    }

    public function newIncome($productId, $arrivalQuantity, $byPrice, $supplier)
    {
        $income = new IncomingProduct();
        $income->product_id = $productId;
        $income->supplier = $supplier;
        $income->quantity = $arrivalQuantity;
        $income->by_price = $byPrice;
        $income->save();

        return $income;
    }

}
