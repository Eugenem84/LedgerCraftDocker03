<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Repositories\IncomingProductRepository;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\ProductStockRepository;
use App\Repositories\ProductRepository;
use App\Models\IncomingProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $productRepository, $productStockRepository, $incomingProductRepository;

    public function __construct(ProductRepository $productRepository,
                                ProductStockRepository $productStockRepository,
                                IncomingProductRepository $incomingProductRepository)
    {
        $this->productRepository = $productRepository;
        $this->productStockRepository = $productStockRepository;
        $this->incomingProductRepository = $incomingProductRepository;
    }

    public function getByProductCategory($categoryId)
    {
        $products = $this->productRepository->getByCategory($categoryId);
        if ($products){
            return response()->json($products);
        } else {
            return response()->json(['message' => 'товаров не найдено']);
        }
    }

    public function addNew(Request $request)
    {
        $name= $request->input('name');
        $baseSalePrice= $request->input('base_sale_price');
        $productCategoryId= $request->input('product_category_id');
        $productId = $this->productRepository->addNew($name,$baseSalePrice,$productCategoryId);

        // Строка остатка — одна на товар; категория больше не дублируется в остатке
        // (задача 9.3: «где лежит товар» знает products.product_category_id).
        $newProduct = $this->productStockRepository->addNew($productId);
        return response()->json($newProduct, 201);
    }

    /**
     * Приход товара (задача 9.2).
     *
     * Было: пустой ответ 200 (web читал `response.data.message` — то есть ничего),
     * три записи без транзакции и без идемпотентности — повторный запрос удваивал
     * остаток. Стало: явный ответ, всё в одной транзакции и ключ идемпотентности
     * `uuid_id` (его присылает приложение, поэтому повторная отправка батча синка
     * склад не удваивает).
     */
    public function arrival(Request $request)
    {
        $validated = $request->validate([
            // ⚠️ Строки остатка у товара может ещё не быть (товар создан приложением),
            // поэтому проверяем сам товар, а не `product_stocks.product_id`.
            'product_id'       => 'required|integer|exists:products,id',
            'arrival_quantity' => 'required|integer|min:1',
            'by_price'         => 'nullable',
            'base_sale_price'  => 'nullable',
            'supplier'         => 'nullable|string',
            'uuid_id'          => 'nullable|string',
        ]);

        $productId = (int) $validated['product_id'];
        $quantity = (int) $validated['arrival_quantity'];
        $byPrice = $this->toRubles($request->input('by_price')) ?? 0;
        $supplier = (string) ($request->input('supplier') ?? '');
        $baseSalePrice = $this->toRubles($request->input('base_sale_price'));

        // Приход + остаток + цена продажи — одной транзакцией: «пришло частично»
        // быть не должно (раньше три записи жили каждая сама по себе).
        $result = DB::transaction(function () use ($productId, $quantity, $byPrice, $supplier, $baseSalePrice, $request) {
            $arrival = $this->incomingProductRepository->recordArrival(
                $productId,
                $quantity,
                $byPrice,
                $supplier,
                $request->input('uuid_id')
            );

            if ($baseSalePrice !== null) {
                $this->productRepository->arrivalUpdate($productId, $baseSalePrice);
            }

            return $arrival;
        });

        return response()->json([
            'message'             => $result['created'] ? 'Приход сохранён' : 'Приход уже учтён (идемпотентно)',
            'idempotent'          => !$result['created'],
            'incoming_product_id' => $result['id'],
            'product_id'          => $productId,
            'quantity'            => $quantity,
            'stock_quantity'      => $result['stock_quantity'],
        ], $result['created'] ? 201 : 200);
    }

    /**
     * Деньги — целыми рублями (единый стандарт, задача 3.12): «1 000,50» → 1001,
     * пустое/нечисловое → `null`. Логика та же, что в `SyncController::toRubles()`.
     */
    private function toRubles($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (int) round($value);
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

        return is_numeric($normalized) ? (int) round((float) $normalized) : null;
    }

    public function edit(Request $request){
        $id = $request->input('id');
        $newName = $request->input('name');
        $baseSailPrice = $request->input('base_sale_price');
        $storeBalance  = $request->input('store_balance');
        $result = $this->productRepository->edit($id, $newName, $baseSailPrice, $storeBalance);
        if ($result){
            return response()->json(['message' => 'Товар успешно изменен'], 200);
        } else {
            return response()->json(['message' => 'Товар не найден'], 404);
        }
    }
//
    public function delete(Request $request){
        $id = $request->input('productId');
        $productResult = $this->productRepository->delete($id);
        if ($productResult){
            return response()->json(['message' => 'Товар удален'], 200);
        } else {
            return response()->json(['message' => 'Товар не найден'], 404);
        }
        $storeResult = $this->productStockRepository->delete($id);
    }

}
