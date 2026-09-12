<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\IncomingProduct;
use Illuminate\Support\Facades\Schema;

class IncomingProductRepository extends Controller
{
    protected $incoming;
    protected $productStockRepository;

    public function __construct(IncomingProduct $incomingProduct, ProductStockRepository $productStockRepository)
    {
        $this->incoming = $incomingProduct;
        $this->productStockRepository = $productStockRepository;
    }

    /**
     * Проводит приход товара **ровно один раз** (задача 9.2).
     *
     * Идемпотентность — по клиентскому id прихода (`uuid_id`, миграция 3.5): тем же
     * ключом дедуплицирует синк, поэтому повторная отправка батча (или повторный
     * `/arrival_product` с тем же `uuid_id`) правит уже созданную запись, но склад
     * **не** увеличивает — иначе приход удвоил бы остаток.
     *
     * Строку остатка заводим при необходимости (товар мог приехать из синка без неё).
     * Вызывать нужно внутри транзакции: приход и остаток должны примениться вместе.
     *
     * @return array{id: int|string, created: bool, stock_quantity: int}
     */
    public function recordArrival($productId, $arrivalQuantity, $byPrice, $supplier = '', $uuidId = null): array
    {
        $productId = (int) $productId;
        $quantity = (int) $arrivalQuantity;
        $byPrice = (int) $byPrice;
        $supplier = (string) ($supplier ?? '');
        $uuidId = $this->normalizedUuid($uuidId);

        $existing = $this->findByUuid($uuidId);

        if ($existing) {
            // Тот же приход пришёл снова: правим запись, остаток не трогаем.
            $existing->quantity = $quantity;
            $existing->by_price = $byPrice;
            $existing->supplier = $supplier;
            $existing->save();

            return [
                'id' => $existing->id,
                'created' => false,
                'stock_quantity' => (int) $this->productStockRepository->ensureForProduct($productId)->quantity,
            ];
        }

        $income = new IncomingProduct();
        $income->product_id = $productId;
        $income->supplier = $supplier;
        $income->quantity = $quantity;
        $income->by_price = $byPrice;

        // Клиентский id прихода — ключ идемпотентности (`uuid_id`, задача 3.5).
        if ($uuidId !== null && Schema::hasColumn('incoming_products', 'uuid_id')) {
            $income->uuid_id = $uuidId;
        }

        $income->save();

        $stock = $this->productStockRepository->arrival($productId, $quantity);

        return [
            'id' => $income->id,
            'created' => true,
            'stock_quantity' => (int) $stock->quantity,
        ];
    }

    /** Уже приходовали этот приход (тот же `uuid_id`)? */
    private function findByUuid($uuidId): ?IncomingProduct
    {
        if ($uuidId === null || !Schema::hasColumn('incoming_products', 'uuid_id')) {
            return null;
        }

        return IncomingProduct::where('uuid_id', $uuidId)->first();
    }

    /**
     * `uuid_id` в PostgreSQL — тип `uuid`, поэтому строка «arrival-1» уронила бы запрос
     * целиком (`SQLSTATE 22P02`), а операция синка зациклилась бы в очереди как
     * `DATABASE_ERROR`. Клиент всегда присылает UUID (`uuidv4`), но защищаемся: мусорный
     * id просто не участвует в дедупликации — приход пройдёт как новый, данные не потеряются.
     */
    private function normalizedUuid($uuidId): ?string
    {
        if ($uuidId === null) {
            return null;
        }

        $value = (string) $uuidId;

        $isUuid = preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );

        return $isUuid ? $value : null;
    }
}
