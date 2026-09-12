<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository extends Controller
{
    /**
     * Товары категории вместе со складом и ценами (задачи 9.2/9.3).
     *
     * Раньше отдавались только поля `products`, поэтому web-склад не видел остатка,
     * а `buy_product_prices`/`sales_products_prices` не читались ни в одном расчёте.
     * Теперь в ответе: `quantity` (остаток), `buy_price` (последняя закупка) и
     * `last_sale_price` (последняя цена продажи этого товара) — web-склад показывает
     * всё три величины.
     *
     * `DISTINCT ON` — синтаксис PostgreSQL (сервер всегда на нём): «последняя по
     * `created_at` строка на товар» для истории цен; оконные функции не используем,
     * чтобы правило было читаемым.
     */
    public function getByCategory($categoryId)
    {
        return DB::select("
            SELECT products.*,
                   COALESCE(stock.quantity, 0) AS quantity,
                   buy.buy_price               AS buy_price,
                   sales.sale_price            AS last_sale_price
            FROM products
            LEFT JOIN product_stocks stock ON stock.product_id = products.id
            LEFT JOIN (
                SELECT DISTINCT ON (product_id) product_id, buy_price
                FROM buy_product_prices
                ORDER BY product_id, created_at DESC, id DESC
            ) buy ON buy.product_id = products.id
            LEFT JOIN (
                SELECT DISTINCT ON (product_id) product_id, sale_price
                FROM sales_products_prices
                ORDER BY product_id, created_at DESC, id DESC
            ) sales ON sales.product_id = products.id
            WHERE products.product_category_id = :category_id
              AND products.deleted_at IS NULL
            ORDER BY products.name
        ", ['category_id' => $categoryId]);
    }

    public function getProduct($id)
    {
        return Product::find($id);
    }

    /**
     * Принадлежит ли товар пользователю (задача 11.7, бывший O-6).
     *
     * Цепочка владельца — та же, что в синке (задача 3.10): `products` →
     * `product_categories` → `specializations.user_id`. «Ничьи» звенья (категория или
     * владелец = NULL — данные до 3.10) считаются общими, как в `SyncController`, иначе
     * legacy-товары стали бы недоступны для прихода.
     */
    public function belongsToUser(int $productId, int $userId): bool
    {
        return DB::table('products')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->leftJoin('specializations', 'specializations.id', '=', 'product_categories.specialization_id')
            ->where('products.id', $productId)
            ->where(fn($query) => $query
                ->where('specializations.user_id', $userId)
                ->orWhereNull('specializations.user_id'))
            ->exists();
    }

    /**
     * Последняя закупочная цена товара — себестоимость для позиций заказа (задачи 9.5/9.6).
     *
     * Источник — `buy_product_prices` (её пишет приход, задача 9.2); если истории закупок
     * ещё нет, берём цену последнего прихода (`incoming_products.by_price`). `null` — цены
     * нет вовсе: маржа по позиции не считается, и это честнее, чем подставить 0.
     *
     * Нужна там, где заказ создаётся не приложением (web-форма) или старым клиентом,
     * который `buy_price` в строке заказа ещё не присылает.
     */
    public function lastBuyPrice(int $productId): ?int
    {
        $price = DB::table('buy_product_prices')
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('buy_price');

        if ($price !== null) {
            return (int) $price;
        }

        $arrivalPrice = DB::table('incoming_products')
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('by_price');

        return $arrivalPrice === null ? null : (int) $arrivalPrice;
    }

    public function addNew($name, $base_sale_price, $productCategoryId)
    {
        $product = new Product();
        $product->name = $name;
        $product->base_sale_price = $base_sale_price;
        $product->product_category_id = $productCategoryId;
        $product->save();
        return $product->id;
    }

    public function arrivalUpdate($productId, $baseSalePrice)
    {
        $product = Product::find($productId);
        $product->base_sale_price = $baseSalePrice;
        $product->save();
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if ($product){
            $product->delete();
            return true;
        }
        return false;
    }

    public function edit($id, $newName, $baseSalePrice, $storeBalance)
    {
        $product = Product::with('stock')->find($id);
        if (!$product) {
            return false;
        }

        $product->name = $newName;
        $product->base_sale_price = $baseSalePrice;

        if ($product->stock) {
            $product->stock->quantity = $storeBalance;
            $product->stock->save();
        }
        $product->save();

        return true;

//        $product = Product::with('stock')->find($id);
//        if ($product){
//            $product->name = $newName;
//            $product->base_sale_price = $baseSalePrice;
//            $product->stock->quantity = $storeBalance;
//            $product->save();
//            return true;
//        } else {
//            return false;
//        }
    }
}
