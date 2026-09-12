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
    /**
     * Остатки категории для web-склада (задача 9.3).
     *
     * «Где лежит товар» определяется товаром (`products.product_category_id`) —
     * собственного `product_categories_id` у строки остатка больше нет (дубль
     * источника убран миграцией `2026_09_16_000000`).
     *
     * `LEFT JOIN` (а не `INNER`): товар без строки остатка тоже должен быть в списке
     * (показываем 0). Колонки выбраны совместимо с прежним ответом (`SELECT *` из
     * двух таблиц): `id`/`name`/`base_sale_price` — из товара, `quantity` — из остатка.
     */
    public function getByProductCategory($categoryId)
    {
        return DB::select("
            SELECT products.id                    AS id,
                   products.name                  AS name,
                   products.base_sale_price       AS base_sale_price,
                   products.product_category_id   AS product_category_id,
                   products.deleted_at            AS deleted_at,
                   COALESCE(product_stocks.quantity, 0) AS quantity,
                   product_stocks.id              AS product_stock_id
            FROM products
            LEFT JOIN product_stocks ON product_stocks.product_id = products.id
            WHERE products.product_category_id = :product_category_id
              AND products.deleted_at IS NULL
            ORDER BY products.name
        ", ['product_category_id' => $categoryId]);
    }

    /**
     * Строка остатка для товара (0 шт.). Категория здесь больше не хранится —
     * её знает сам товар (задача 9.3).
     */
    public function addNew($productId)
    {
        return $this->ensureForProduct($productId);
    }

    /**
     * Строка остатка товара — по одной на товар (`Product::stock()` — hasOne).
     *
     * Задача 9.2: товар может быть заведён приложением (уехать в синк), и тогда строки
     * остатка у него нет — прежний `/arrival_product` падал с «Product stock N not found»,
     * а синк-приход вообще не мог обновить склад. Поэтому строку заводим по требованию.
     *
     * Задача 9.3: категория товара здесь не дублируется — «где лежит товар» знает
     * `products.product_category_id` (прежнего `product_categories_id` больше нет).
     */
    public function ensureForProduct($productId): ProductStock
    {
        $productStock = ProductStock::where('product_id', $productId)->first();

        if ($productStock) {
            return $productStock;
        }

        $productStock = new ProductStock();
        $productStock->product_id = $productId;
        $productStock->quantity = 0;
        $productStock->save();

        return $productStock;
    }

    /**
     * Приход на склад: остаток увеличивается на количество прихода.
     *
     * Увеличение происходит **ровно один раз на приход** — за это отвечает
     * `IncomingProductRepository::recordArrival()` (идемпотентность по `uuid_id`),
     * а не эта функция: повторная отправка батча синка не должна удваивать остаток.
     */
    public function arrival($productId, $arrivalQuantity)
    {
        $productStock = $this->ensureForProduct($productId);
        $productStock->quantity += (int) $arrivalQuantity;
        $productStock->save();

        return $productStock;
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
    public function delete($product_id)
    {
        $productCategory = ProductCategory::where('product_id', $product_id)->first();
        if ($productCategory){
            //$productCategory->products()->delete();
            $productCategory->delete();
            return true;
        } else {
            return false;
        }
    }
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
