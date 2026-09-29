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
     * Остаток товара как производная величина (правка владельца 29.09.2026).
     *
     * ⚠️ Раньше «сколько лежит» хранилось в `product_stocks`, и это число было
     * непоследовательным: web-заказ его списывал, а продажа из мобильного приложения
     * (приходит синком в `order_product`) — нет. Поэтому web-склад расходился с телефоном.
     *
     * Теперь остаток считается из движений, той же формулой, что на устройстве:
     *
     *   остаток = Σ приходов (`incoming_products`) − Σ продаж (`order_product`
     *             в не удалённых заказах)
     *
     * Формула живёт в одном месте и вставляется в выборки товаров (`getByProductCategory`
     * здесь и `ProductRepository::getByCategory`). `product_stocks` остаётся legacy-таблицей
     * (совместимость с `/sync-updates`), но остаток больше не определяет.
     */
    public const QUANTITY_SQL = "
        COALESCE((SELECT SUM(arrival.quantity) FROM incoming_products arrival
                  WHERE arrival.product_id = products.id), 0)
        - COALESCE((SELECT SUM(sold.quantity) FROM order_product sold
                    JOIN orders ON orders.id = sold.order_id
                    WHERE sold.product_id = products.id
                      AND orders.deleted_at IS NULL), 0)
    ";

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
        $quantitySql = self::QUANTITY_SQL;

        return DB::select("
            SELECT products.id                    AS id,
                   products.name                  AS name,
                   products.base_sale_price       AS base_sale_price,
                   products.product_category_id   AS product_category_id,
                   products.deleted_at            AS deleted_at,
                   ($quantitySql) AS quantity,
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
     * Остаток одного товара — та же производная формула (`QUANTITY_SQL`).
     *
     * Нужен там, где раньше читали `product_stocks.quantity`: ответ прихода
     * (`IncomingProductRepository::recordArrival`) и проверка доступности товара
     * в web-заказе (`OrderRepository`).
     */
    public function quantityForProduct($productId): int
    {
        $quantitySql = self::QUANTITY_SQL;

        $row = DB::selectOne("
            SELECT ($quantitySql) AS quantity
            FROM products
            WHERE products.id = :product_id
        ", ['product_id' => $productId]);

        return (int) ($row->quantity ?? 0);
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
