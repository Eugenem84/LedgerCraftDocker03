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

    public function newIncome($product_id, $supplier, $quantity, $buy_price)
    {
        $income = new IncomingProduct();
        $income->product_id = $product_id;
        $income->supplier = $supplier;
        $income->quantity = $quantity;
        $income->by_price = $buy_price;
        $income->save();
    }

}
