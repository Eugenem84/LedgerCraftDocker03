<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Repositories\IncomingProductRepository;
use App\Repositories\ProductStockRepository;
use App\Repositories\ProductRepository;

class SyncController extends Controller
{
    private array $tables = [
        'clients',
        'specializations',
        'orders',
        'equipment_models',
        'incoming_products',
        'materials',
        'order_product',
        'order_service',
        'products',
        'product_categories',
        'product_stocks',
        'categories',
        'services',
        'buy_product_prices',
        'sales_products_prices',
    ];

    /** Приходы: увеличение склада должно происходить ровно один раз (задача 9.2). */
    private IncomingProductRepository $incomingProducts;

    /** Склад: строка остатка на товар (задачи 9.2/9.3). */
    private ProductStockRepository $productStocks;

    /** Товары: последняя закупка — себестоимость позиции заказа (задачи 9.5/9.6). */
    private ProductRepository $products;

    public function __construct(
        IncomingProductRepository $incomingProducts,
        ProductStockRepository $productStocks,
        ProductRepository $products
    ) {
        $this->incomingProducts = $incomingProducts;
        $this->productStocks = $productStocks;
        $this->products = $products;
    }

    public function sync(Request $request)
    {
        $operations = $request->input('operations', []);
        $results = [
            'synced' => [],
            'errors' => [],
        ];
        $syncId = $request->header('X-Sync-ID');

        // Владелец данных (задача 3.10): маршрут под `auth:sanctum`, поэтому пользователь
        // есть всегда; `null` возможен только при внутреннем вызове (тесты/консоль).
        $userId = $this->userId($request);

        DB::transaction(function () use ($operations, &$results, $syncId, $userId) {
            foreach ($operations as $op) {
                $table   = $op['table'] ?? null;
                $type    = $op['type'] ?? null;
                $payload = $op['payload'] ?? null;
                $localId = $payload['uuid_id'] ?? $payload['local_id'] ?? $op['id'] ?? null;

                if (!$table || !$type || !$payload || !in_array($table, $this->tables)) {
                    $results['errors'][] = ['local_id' => $localId, 'error' => 'Invalid operation structure or table.'];
                    continue;
                }

                // SAVEPOINT на операцию (перенос из Go-сайдкара, задача 3.11):
                // одна битая операция не откатывает весь батч и не «вешает» транзакцию
                // (в PostgreSQL после ошибки транзакция переходит в aborted state).
                DB::statement('SAVEPOINT sync_op');

                try {
                    switch ($type) {
                        case 'insert':
                            $this->insertRecord($table, $payload, $results, $syncId, $localId, $userId);
                            break;
                        case 'update':
                            $this->updateRecord($table, $payload, $results, $syncId, $localId, $userId);
                            break;
                        case 'delete':
                            $this->deleteRecord($table, $payload, $results, $syncId, $localId, $userId);
                            break;
                        default:
                            $results['errors'][] = ['local_id' => $localId, 'error' => "Unsupported operation type: {$type}"];
                    }

                    DB::statement('RELEASE SAVEPOINT sync_op');
                } catch (QueryException $e) {
                    $this->rollbackSavepoint();
                    $results['errors'][] = ['local_id' => $localId, 'error' => 'DATABASE_ERROR', 'details' => ['message' => $e->getMessage(), 'sql' => $e->getSql(), 'bindings' => $e->getBindings()]];
                    Log::error('Sync DB operation failed', ['operation' => $op, 'exception' => $e]);
                } catch (\Throwable $e) {
                    $this->rollbackSavepoint();
                    $results['errors'][] = ['local_id' => $localId, 'error' => 'GENERAL_ERROR', 'details' => ['message' => $e->getMessage()]];
                    Log::error('Sync operation failed', ['operation' => $op, 'exception' => $e]);
                }
            }
        });

        return response()->json($results);
    }

    /**
     * Откатывает текущую операцию к SAVEPOINT'у и снимает его.
     * Сбой самого отката не должен ломать батч — остальные операции продолжаем.
     */
    private function rollbackSavepoint(): void
    {
        try {
            DB::statement('ROLLBACK TO SAVEPOINT sync_op');
            DB::statement('RELEASE SAVEPOINT sync_op');
        } catch (\Throwable $e) {
            Log::error('Sync savepoint rollback failed', ['exception' => $e]);
        }
    }

    public function fetchUpdates(Request $request)
    {
        Log::debug('SYNC_UPDATES REQUEST', ['query' => $request->query()]);
        $syncId = $request->header('X-Sync-ID');
        $table = $request->query('table');
        $since = $request->query('since');

        if (!$table || !in_array($table, $this->tables, true)) {
            return response()->json(['error' => 'Invalid or missing table'], 400);
        }

        $query = DB::table($table);

        $sinceCarbon = null;

        if ($since !== null && $since !== '') {
            try {
                $sinceCarbon = Carbon::createFromTimestampMs((int)$since);
                $query->where('updated_at', '>', $sinceCarbon);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Invalid since timestamp'], 400);
            }
        }

        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $query->where(fn($q) => $q->where('last_sync_id', '!=', $syncId)->orWhereNull('last_sync_id'));
        }

        // Владелец данных (задача 3.10): устройство видит только записи своего
        // пользователя — как напрямую (`user_id`), так и через цепочку родителей
        // (`specializations.user_id` / `orders.user_id`).
        $userId = $this->userId($request);

        $this->applyOwnerScope($query, $table, $userId);

        // Soft-deleted строки НЕ отфильтровываем (задача 3.9): они и есть tombstone —
        // клиент по `deleted_at`/`deleted` удаляет запись у себя. Иначе удаление,
        // сделанное на другом устройстве, не «доехало» бы никогда.
        $records = $query->orderBy('updated_at')->get();

        // Единый стандарт времени (задача 3.8): сервер отдаёт timestamps ISO-8601 UTC
        // (`2026-09-12T10:00:00.000000Z`). «Сырое» значение Postgres (`2026-09-12 10:00:00`)
        // клиентский `Date.parse` принимает за ЛОКАЛЬНОЕ время устройства — сравнение версий
        // (last-write-wins) и курсор выдачи смещались бы на часовой пояс.
        $records->each(function ($record) {
            $this->normalizeTimestamps($record);
            // Явный признак удаления: клиенту не нужно самому разбирать `deleted_at`.
            $record->deleted = isset($record->deleted_at) && $record->deleted_at !== null;
        });

        // Таблицы без `deleted_at` удаляются физически — их удаления лежат в tombstone'ах.
        $records = $records->concat($this->fetchTombstones($table, $sinceCarbon, $syncId, $userId));

        return response()->json(['table' => $table, 'count' => $records->count(), 'records' => $records]);
    }

    /**
     * Отдаёт tombstones таблицы как записи-удаления (задача 3.9).
     *
     * Формат записи: `{ id: <server_id>, uuid_id, deleted: true, deleted_at, updated_at }`.
     * `id`/`uuid_id` позволяют клиенту найти локальную строку (по `server_id`
     * или по клиентскому UUID у `order_service`-подобных таблиц).
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function fetchTombstones(string $table, ?Carbon $since, ?string $syncId, ?int $userId = null)
    {
        if ($this->tableHasSoftDeletes($table) || !Schema::hasTable('sync_tombstones')) {
            return collect();
        }

        $query = DB::table('sync_tombstones')->where('table_name', $table);

        // Удаления чужих устройств отдавать нельзя (задача 3.10).
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($since) {
            $query->where('deleted_at', '>', $since);
        }

        // Анти-эхо (задача 3.6): автор удаления свой tombstone не получает.
        if ($syncId) {
            $query->where(fn($q) => $q->where('last_sync_id', '!=', $syncId)->orWhereNull('last_sync_id'));
        }

        return $query->orderBy('deleted_at')->get()->map(function ($tombstone) {
            $deletedAt = $tombstone->deleted_at
                ? Carbon::parse($tombstone->deleted_at)->toJSON()
                : null;

            return [
                'id'         => $tombstone->record_id,
                'uuid_id'    => $tombstone->uuid_id,
                'deleted'    => true,
                'deleted_at' => $deletedAt,
                'updated_at' => $deletedAt,
            ];
        });
    }

    /**
     * Приводит timestamps записи к ISO-8601 UTC (единый стандарт, задача 3.8).
     * Работает по ссылке: query builder отдаёт `stdClass`, мутируем его поля.
     */
    private function normalizeTimestamps(object $record): void
    {
        foreach (['created_at', 'updated_at', 'deleted_at'] as $column) {
            if (!isset($record->$column) || !is_string($record->$column)) {
                continue;
            }

            try {
                $record->$column = Carbon::parse($record->$column)->toJSON();
            } catch (\Throwable $e) {
                Log::warning('Sync: не удалось разобрать timestamp', [
                    'column' => $column,
                    'value'  => $record->$column,
                ]);
            }
        }
    }

    /**
     * Момент операции с секундной точностью.
     *
     * В БД timestamps хранятся как `timestamp(0)`, поэтому и ответ `/sync`, и запись
     * должны использовать одно и то же значение: иначе клиент сохранил бы «свою»
     * версию записи, которой на сервере нет (задача 3.8).
     */
    private function syncNow(): Carbon
    {
        return Carbon::now()->startOfSecond();
    }

    private function insertRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId, ?int $userId = null): void
    {
        // Служебные поля клиента не должны попадать в реальные колонки таблиц:
        // `id`/`local_id`/`uuid_id`, а также `server_id` и сигнальные `*_server_id`
        // (вырезание перенесено из Go-сайдкара, D1/задача 3.11).
        // Деньги приводим к целым рублям (задача 3.12): клиент мог прислать строку,
        // «1 000,50» или пустую строку — в integer-колонку это не влезло бы.
        $payload = $this->normalizeMoney($table, $this->stripClientFields($payload));
        $payload = $this->mapSpecializationName($table, $payload);
        $payload = $this->withSpecializationDefaults($table, $payload);

        // Чужому пользователю не позволяем писать в свои данные (задача 3.10):
        // проверяем владельца родительских записей из payload.
        if (!$this->payloadParentsBelongToUser($table, $payload, $userId)) {
            $results['errors'][] = ['local_id' => $localId, 'error' => 'FORBIDDEN_NOT_OWNER'];
            return;
        }

        // Одна и та же секунда идёт и в запись, и в ответ (задача 3.8).
        $now = $this->syncNow();

        // Специальная обработка для orders: картаем только реально существующие колонки
        if ($table === 'orders') {
            $data = [
                'specialization_id' => $payload['specialization_id'] ?? null,
                'client_id'        => $payload['client_id'] ?? null,
                'hours'            => $payload['hours'] ?? null,
                'minutes'          => $payload['minutes'] ?? null,
                'total_amount'     => $payload['total_amount'] ?? null,
                'comments'         => $payload['comments'] ?? null,
                // Универсальный идентификатор объекта (Фаза 10, задача 10.9):
                // VIN/госномер, серийник рамы, адрес объекта.
                'equipment_identifier' => $payload['equipment_identifier'] ?? null,
                // materials сейчас не синкаем с клиента, пусть будет NULL
            ];

            // Владелец данных (задача 3.10): клиент `user_id` не присылает — проставляем
            // сами, иначе заказ после синка «терял» владельца и не находился по пользователю.
            if ($userId !== null && Schema::hasColumn($table, 'user_id')) {
                $data['user_id'] = $userId;
            }

            if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
                $data['last_sync_id'] = $syncId;
            }
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            $newId = $this->upsertRecord($table, $data, $localId);

            $results['synced'][] = [
                'type'       => 'insert',
                'local_id'   => $localId,
                'server_id'  => $newId,
                // Версия записи на сервере (задача 3.8): клиент сохранит её локально,
                // чтобы более старая копия не «воскрешала» запись (last-write-wins).
                'updated_at' => $now->toJSON(),
            ];

            return;
        }

        // Специальная обработка для order_service:
        // на сервере это связывающая таблица без собственного PK и timestamps,
        // поэтому идемпотентность — по натуральному ключу `order_id + service_id`.
        if ($table === 'order_service') {
            // order_id и service_id приходят уже как server-side ID (см. fkTransformationMap на фронте)
            $orderId   = $payload['order_id']   ?? null;
            $serviceId = $payload['service_id'] ?? null;

            // По умолчанию количество = 1, а цена берётся из таблицы services
            $quantity  = $payload['quantity'] ?? 1;
            $salePrice = $payload['sale_price'] ?? null;

            if ($salePrice === null && $serviceId !== null) {
                $salePrice = DB::table('services')
                    ->where('id', $serviceId)
                    ->value('price');
            }

            $data = [
                'sale_price' => $salePrice,
                'quantity'   => $quantity,
                'updated_at' => $now,
            ];

            // `uuid_id` — клиентский id строки связки: по нему клиент сопоставляет
            // свою запись с серверной (аналог server_id, задача 3.5).
            if (Schema::hasColumn($table, 'uuid_id')) {
                $data['uuid_id'] = $localId;
            }

            // Связку могли удалить раньше (soft-delete), а теперь добавляют снова —
            // натуральный ключ тот же, поэтому «оживляем» строку (задача 3.9).
            if (Schema::hasColumn($table, 'deleted_at')) {
                $data['deleted_at'] = null;
            }

            // Анти-эхо (задача 3.6): помечаем строку устройством-автором, чтобы оно
            // не получило своё же изменение обратно в sync-updates.
            if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
                $data['last_sync_id'] = $syncId;
            }

            $existing = DB::table($table)
                ->where('order_id', $orderId)
                ->where('service_id', $serviceId)
                ->first();

            if ($existing) {
                // Повторная отправка того же INSERT — обновляем строку, не дублируем.
                DB::table($table)
                    ->where('order_id', $orderId)
                    ->where('service_id', $serviceId)
                    ->update($data);
            } else {
                $data['order_id']   = $orderId;
                $data['service_id'] = $serviceId;
                $data['created_at'] = $now;

                DB::table($table)->insert($data);
            }

            // Если у связки всё-таки есть собственный `id` — возвращаем его,
            // иначе null (текущая схема): идемпотентность обеспечил натуральный ключ.
            $serverId = Schema::hasColumn($table, 'id')
                ? DB::table($table)->where('order_id', $orderId)->where('service_id', $serviceId)->value('id')
                : null;

            $results['synced'][] = [
                'type'       => 'insert',
                'local_id'   => $localId,
                'server_id'  => $serverId,
                'updated_at' => $now->toJSON(),
            ];

            return;
        }

        // Специальная обработка для incoming_products (задача 9.2): приход меняет склад,
        // и делает это **ровно один раз** — идемпотентность по клиентскому `uuid_id`
        // (`IncomingProductRepository::recordArrival()`). Поэтому повторная отправка
        // батча не удваивает `product_stocks.quantity`, а строка прихода остаётся одна.
        if ($table === 'incoming_products') {
            $productId = $payload['product_id'] ?? null;
            $quantity = (int) ($payload['quantity'] ?? 0);

            if ($productId === null) {
                $results['errors'][] = ['local_id' => $localId, 'error' => 'MISSING_PRODUCT_ID'];
                return;
            }

            if ($quantity < 1) {
                $results['errors'][] = ['local_id' => $localId, 'error' => 'INVALID_QUANTITY'];
                return;
            }

            $arrival = $this->incomingProducts->recordArrival(
                (int) $productId,
                $quantity,
                (int) ($payload['by_price'] ?? 0),
                (string) ($payload['supplier'] ?? ''),
                $localId
            );

            // Анти-эхо (задача 3.6): помечаем строку устройством-автором, чтобы оно
            // не получило свой же приход обратно в `sync-updates`.
            if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
                DB::table($table)->where('id', $arrival['id'])->update(['last_sync_id' => $syncId]);
            }

            $results['synced'][] = [
                'type'       => 'insert',
                'local_id'   => $localId,
                'server_id'  => $arrival['id'],
                'updated_at' => $now->toJSON(),
                // Сколько стало на складе — информационно: клиент обновляет остаток и
                // сам (офлайн), а серверное значение придёт выгрузкой `product_stocks`.
                'stock_quantity' => $arrival['stock_quantity'],
            ];

            return;
        }

        // Обработка по умолчанию для остальных таблиц
        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
        }

        // Себестоимость позиции (задачи 9.5/9.6): если клиент её не прислал, берём
        // последнюю закупку товара (`buy_product_prices`, иначе последний приход).
        // Иначе маржа заказа считалась бы как «выручка = прибыль» — старая ошибка отчётов.
        // У ручных позиций (`materials`) источника нет: закупку вводит мастер в форме.
        if ($table === 'order_product' && ($payload['buy_price'] ?? null) === null) {
            $payload['buy_price'] = $this->products->lastBuyPrice((int) ($payload['product_id'] ?? 0));
        }

        // Владелец данных (задача 3.10): у таблиц с `user_id` (specializations)
        // проставляем его сами — клиент этого поля не знает.
        if ($userId !== null && Schema::hasColumn($table, 'user_id')) {
            $payload['user_id'] = $userId;
        }

        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $newId = $this->upsertRecord($table, $payload, $localId);

        // Товар получает строку остатка (задача 9.3): «где лежит товар» — это товар,
        // а «сколько лежит» — одна строка на товар. Иначе web-склад и выгрузка в
        // приложение видели бы отсутствующую строку вместо нуля. Повтор — идемпотентен.
        if ($table === 'products' && $newId !== null) {
            $this->productStocks->ensureForProduct($newId);
        }

        $results['synced'][] = [
            'type'       => 'insert',
            'local_id'   => $localId,
            'server_id'  => $newId,
            'updated_at' => $now->toJSON(),
        ];
    }

    private function updateRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId, ?int $userId = null): void
    {
        // Для обновления запись на сервере обязана содержать server-side ID
        if (!array_key_exists('id', $payload)) {
            $results['errors'][] = [
                'local_id' => $localId,
                'error' => 'MISSING_ID_FOR_UPDATE',
            ];
            return;
        }

        $id = $payload['id'];

        // Чужую запись не обновляем (задача 3.10). Отвечаем как на несуществующую,
        // чтобы не подсказывать, что запись вообще есть.
        if (!$this->recordBelongsToUser($table, $id, $userId)) {
            $results['errors'][] = [
                'local_id' => $localId,
                'error' => 'RECORD_NOT_FOUND',
                'details' => ['id' => $id],
            ];
            return;
        }

        $payload = $this->normalizeMoney($table, $this->stripClientFields($payload));
        $payload = $this->mapSpecializationName($table, $payload);

        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
        }

        // Владелец данных (задача 3.10): запись, созданную до 3.10 («ничья»),
        // первый же пользователь, который её правит, «забирает» себе.
        if ($userId !== null && Schema::hasColumn($table, 'user_id')) {
            $payload['user_id'] = $userId;
        }

        // Сервер — источник версии: клиент применит её у себя после ответа (задача 3.8).
        $now = $this->syncNow();
        $payload['updated_at'] = $now;

        if (!DB::table($table)->where('id', $id)->exists()) {
            $results['errors'][] = [
                'local_id' => $localId,
                'error' => 'RECORD_NOT_FOUND',
                'details' => ['id' => $id],
            ];
            return;
        }

        DB::table($table)->where('id', $id)->update($payload);

        // Подтверждаем всегда — даже если значения не изменились (affected = 0).
        // Клиент считает операцию доставленной только при явном ответе по ней
        // (задача 3.5), поэтому «пустой» ответ заставил бы его повторять вечно.
        $results['synced'][] = [
            'type'       => 'update',
            'local_id'   => $localId,
            'server_id'  => $id,
            'updated_at' => $now->toJSON(),
        ];
    }

    private function deleteRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId, ?int $userId = null): void
    {
        $id = $payload['id'] ?? null;

        // Чужую запись не удаляем (задача 3.10) — отвечаем как на несуществующую.
        // Владельца проверяем только у реально существующей строки: повторное удаление
        // уже удалённой записи — норма (идемпотентность, задача 3.5).
        if ($id !== null && $this->recordExists($table, $id) && !$this->recordBelongsToUser($table, $id, $userId)) {
            $results['errors'][] = [
                'local_id' => $localId,
                'error' => 'RECORD_NOT_FOUND',
                'details' => ['id' => $id],
            ];
            return;
        }

        // Время операции: у soft-delete это ещё и новая версия записи (задача 3.8).
        $now = $this->syncNow();

        // order_service — связка без собственного PK: удаляем по натуральному
        // ключу `order_id + service_id` (серверные id), который присылает клиент.
        if ($id === null && $table === 'order_service') {
            $orderId   = $payload['order_id'] ?? null;
            $serviceId = $payload['service_id'] ?? null;

            // Владелец связки — её заказ (задача 3.10).
            if ($orderId !== null && $userId !== null
                && !$this->ownedIds('orders', $userId)->where('id', $orderId)->exists()) {
                $results['errors'][] = [
                    'local_id' => $localId,
                    'error' => 'RECORD_NOT_FOUND',
                    'details' => ['id' => $orderId],
                ];
                return;
            }

            if ($orderId !== null && $serviceId !== null) {
                $lineQuery = DB::table($table)
                    ->where('order_id', $orderId)
                    ->where('service_id', $serviceId);

                if ($this->tableHasSoftDeletes($table)) {
                    // У связки есть `deleted_at` — помечаем строку удалённой, чтобы
                    // удаление «доехало» до других устройств (задача 3.9).
                    $updateData = ['deleted_at' => $now, 'updated_at' => $now];
                    if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
                        $updateData['last_sync_id'] = $syncId;
                    }
                    $affected = $lineQuery->update($updateData);
                } else {
                    $affected = $lineQuery->delete();
                }

                // DELETE идемпотентен: отсутствие строки — тоже «применено».
                $results['synced'][] = [
                    'type'       => 'delete',
                    'local_id'   => $localId,
                    'server_id'  => null,
                    'deleted'    => $affected,
                    'updated_at' => $now->toJSON(),
                ];

                return;
            }
        }

        if ($id === null) {
            $results['errors'][] = [
                'local_id' => $localId,
                'error' => 'MISSING_ID_FOR_DELETE',
            ];
            return;
        }

        $query = DB::table($table)->where('id', $id);

        if ($this->tableHasSoftDeletes($table)) {
            // Soft-delete — это изменение записи, поэтому двигаем и версию (`updated_at`):
            // иначе «удалено» осталось бы невидимым для выдачи по курсору (задача 3.9).
            $updateData = ['deleted_at' => $now, 'updated_at' => $now];
            if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
                $updateData['last_sync_id'] = $syncId;
            }
            $query->update($updateData);
        } else {
            // Физическое удаление: строка исчезает, поэтому факт удаления
            // фиксируем в tombstone'ах — иначе второе устройство о нём не узнает.
            $query->delete();
            $this->recordTombstone($table, $id, $payload['uuid_id'] ?? null, $now, $syncId, $userId);
        }

        // Подтверждаем всегда: повторный DELETE уже удалённой записи — норма.
        // `updated_at` — момент операции (у hard-delete записи на сервере уже нет,
        // клиент ничего не применяет: локальная строка удалена вместе с операцией).
        $results['synced'][] = [
            'type'       => 'delete',
            'local_id'   => $localId,
            'server_id'  => $id,
            'updated_at' => $now->toJSON(),
        ];
    }

    /**
     * Идемпотентная вставка (задача 3.5).
     *
     * Ключ идемпотентности — клиентский идентификатор записи (`local_id`),
     * который хранится в колонке `uuid_id`. Если запись с таким `uuid_id` уже
     * есть, она обновляется (повторная отправка батча не создаёт дубль), иначе
     * вставляется новая. `created_at` при повторной отправке не переписываем.
     *
     * @return int|string|null серверный id записи
     */
    private function upsertRecord(string $table, array $data, ?string $localId): int|string|null
    {
        if ($localId === null || !Schema::hasColumn($table, 'uuid_id')) {
            // Дедуплицировать нечем (нет ключа или БД без миграции `uuid_id`):
            // вставляем как раньше — от «осиротевших» дублей защищает SAVEPOINT.
            return DB::table($table)->insertGetId($data);
        }

        $existing = DB::table($table)->where('uuid_id', $localId)->first();

        if ($existing) {
            $update = $data;
            unset($update['created_at']);

            // Повторный INSERT мог быть отправлен уже после удаления записи (офлайн):
            // «оживляем» строку явным сбросом `deleted_at` (задача 3.9).
            if (Schema::hasColumn($table, 'deleted_at')) {
                $update['deleted_at'] = null;
            }

            DB::table($table)->where('uuid_id', $localId)->update($update);

            return $existing->id ?? null;
        }

        $data['uuid_id'] = $localId;

        return DB::table($table)->insertGetId($data);
    }

    /**
     * Вырезает служебные поля клиента из payload: `id`, `local_id`, `uuid_id`,
     * `server_id`, `share_token` и сигнальные `*_server_id`. Перенос из Go-сайдкара
     * (D1, 3.11): иначе `server_id`/`*_server_id` улетали бы в реальные колонки таблиц.
     *
     * `share_token` — серверное поле (задача 9.4): токен публичной ссылки создаёт
     * только сервер, а клиент прислал бы `null` (он токена не знает) и своим же
     * обновлением заказа затёр бы уже выданную клиенту ссылку.
     */
    private function stripClientFields(array $payload): array
    {
        $clean = [];

        foreach ($payload as $key => $value) {
            if (in_array($key, ['id', 'local_id', 'uuid_id', 'server_id', 'share_token'], true)) {
                continue;
            }
            if (str_ends_with($key, '_server_id')) {
                continue;
            }
            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * `specializations` хранит название в колонке `specializationName`, а клиент
     * шлёт `name` (выдача `/sync-updates` отдаёт `specializationName`, и `api.js`
     * переименовывает его в `name`). Без сопоставления INSERT/UPDATE специализации
     * падал на «column name does not exist», профиль не получал server_id, и
     * мульти-профиль Фазы 10 не синкался бы вовсе.
     */
    private function mapSpecializationName(string $table, array $payload): array
    {
        if (
            $table === 'specializations'
            && array_key_exists('name', $payload)
            && !array_key_exists('specializationName', $payload)
        ) {
            $payload['specializationName'] = $payload['name'];
            unset($payload['name']);
        }

        return $payload;
    }

    /**
     * Значения по умолчанию для INSERT специализации из синка.
     *
     * У `specializations` есть легаси-колонка `popularCounter` (NOT NULL, без
     * default в исходной миграции): офлайн созданный профиль приезжал из очереди
     * без неё, и вся операция падала на «null value in column popularCounter
     * violates not-null constraint» — специализация не получала server_id.
     * Ставим 0 только при INSERT (при UPDATE не трогаем, чтобы не затирать значение).
     */
    private function withSpecializationDefaults(string $table, array $payload): array
    {
        if ($table === 'specializations' && !array_key_exists('popularCounter', $payload)) {
            $payload['popularCounter'] = 0;
        }

        return $payload;
    }

    /**
     * Денежные колонки по таблицам (задача 3.12). Все деньги на сервере — целые рубли,
     * поэтому любые значения клиента (строки, «1 000,50», пустая строка) приводим к int.
     */
    private const MONEY_COLUMNS = [
        'services'            => ['price'],
        'materials'           => ['price', 'buy_price'],
        'orders'              => ['total_amount'],
        'order_service'       => ['sale_price'],
        'order_product'       => ['sale_price', 'buy_price'],
        'products'            => ['base_sale_price'],
        'incoming_products'   => ['by_price'],
        'buy_product_prices'  => ['buy_price'],
        'sales_products_prices' => ['sale_price'],
    ];

    /**
     * Приводит денежные поля payload к целым рублям (задача 3.12).
     *
     * Раньше клиент мог отправить `price: ''` или `'1 000,50'`, а серверная колонка
     * была VARCHAR — «как-нибудь да влезет», и потом это кастовалось в каждом расчёте.
     * Теперь колонки — integer, поэтому нормализуем на входе (одно место, один стандарт).
     */
    private function normalizeMoney(string $table, array $payload): array
    {
        foreach (self::MONEY_COLUMNS[$table] ?? [] as $column) {
            if (!array_key_exists($column, $payload)) {
                continue;
            }

            // «Цена не задана» у услуги исторически = пустая строка, а колонка NOT NULL.
            $default = ($table === 'services' && $column === 'price') ? 0 : null;
            $payload[$column] = $this->toRubles($payload[$column], $default);
        }

        return $payload;
    }

    /**
     * Значение → целые рубли. Нечисловое/пустое значение превращается в `$default`
     * (а не в 0), чтобы не потерять факт «поле не заполнено» там, где колонка nullable.
     */
    private function toRubles($value, ?int $default = null): ?int
    {
        if ($value === null) {
            return $default;
        }

        if (is_int($value) || is_float($value)) {
            return (int) round($value);
        }

        // «1 000,50» → «1000.50»
        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

        if ($normalized === '' || !is_numeric($normalized)) {
            return $default;
        }

        return (int) round((float) $normalized);
    }

    /**
     * Таблицы, у которых владелец лежит напрямую в `user_id` (задача 3.10).
     */
    private const USER_TABLES = ['orders', 'specializations'];

    /**
     * Ссылки на «родителя» — через них определяется владелец записи (задача 3.10).
     * Первая пара — основная цепочка: по ней фильтруется выдача. Остальные проверяются
     * при записи: нельзя привязать свою запись к чужому заказу/услуге/категории.
     */
    private const OWNER_REFS = [
        'clients'               => ['specialization_id' => 'specializations'],
        'categories'            => ['specialization_id' => 'specializations'],
        'product_categories'    => ['specialization_id' => 'specializations'],
        'equipment_models'      => ['specialization_id' => 'specializations'],
        'services'              => ['category_id' => 'categories'],
        'products'              => ['product_category_id' => 'product_categories'],
        // Остаток адресуется товаром (`Product::stock()` — hasOne): владелец и синк идут
        // цепочкой `product_id` → products → product_categories → specializations.
        // `product_categories_id` из остатка удалён (задача 9.3): «где лежит товар»
        // знает только `products.product_category_id`.
        'product_stocks'        => ['product_id' => 'products'],
        'incoming_products'     => ['product_id' => 'products'],
        'buy_product_prices'    => ['product_id' => 'products'],
        'sales_products_prices' => ['order_id' => 'orders', 'product_id' => 'products'],
        'orders'                => ['specialization_id' => 'specializations', 'client_id' => 'clients'],
        'order_service'         => ['order_id' => 'orders', 'service_id' => 'services'],
        'order_product'         => ['order_id' => 'orders', 'product_id' => 'products'],
        'materials'             => ['order_id' => 'orders'],
    ];

    /**
     * id пользователя из токена (задача 3.10). `null` — только внутренние вызовы.
     */
    private function userId(Request $request): ?int
    {
        $user = $request->user();

        return $user ? (int) $user->getAuthIdentifier() : null;
    }

    /**
     * Подзапрос «id строк таблицы, доступных пользователю» (задача 3.10).
     * Идёт по цепочке родителей до таблицы с `user_id`.
     *
     * «Ничьи» звенья (`user_id`/родитель = NULL — данные до 3.10) считаются общими:
     * иначе записи без владельца исчезли бы из выдачи, а услуга без категории
     * не дала бы добавить её в заказ.
     */
    private function ownedIds(string $table, int $userId)
    {
        if (in_array($table, self::USER_TABLES, true)) {
            return DB::table($table)
                ->select('id')
                ->where(fn($q) => $q->where('user_id', $userId)->orWhereNull('user_id'));
        }

        $refs = self::OWNER_REFS[$table] ?? null;

        if (!$refs) {
            // Владельца нет — не сужаем (служебные таблицы).
            return DB::table($table)->select('id');
        }

        $fk = array_key_first($refs);

        return DB::table($table)
            ->select('id')
            ->where(fn($q) => $q->whereIn($fk, $this->ownedIds($refs[$fk], $userId))->orWhereNull($fk));
    }

    /**
     * Сужает выборку до данных пользователя (задача 3.10).
     *
     * «Ничьи» строки (родитель/`user_id` = NULL — данные до 3.10) остаются видимыми:
     * их нельзя выкинуть из выдачи, не потеряв данные пользователя; разовая привязка
     * legacy-записей к пользователю — отдельная операция, а не молчаливая фильтрация.
     */
    private function applyOwnerScope($query, string $table, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        if (in_array($table, self::USER_TABLES, true)) {
            $query->where(fn($q) => $q->where('user_id', $userId)->orWhereNull('user_id'));

            return;
        }

        $refs = self::OWNER_REFS[$table] ?? null;

        if (!$refs) {
            return;
        }

        $fk = array_key_first($refs);

        $query->where(
            fn($q) => $q->whereIn($fk, $this->ownedIds($refs[$fk], $userId))->orWhereNull($fk)
        );
    }

    /**
     * Существует ли строка (только для таблиц с `id`).
     */
    private function recordExists(string $table, $id): bool
    {
        return Schema::hasColumn($table, 'id') && DB::table($table)->where('id', $id)->exists();
    }

    /**
     * Принадлежит ли запись пользователю (задача 3.10).
     * При `user_id = null` (внутренний вызов) проверка не выполняется.
     */
    private function recordBelongsToUser(string $table, $id, ?int $userId): bool
    {
        if ($userId === null || $id === null || !Schema::hasColumn($table, 'id')) {
            return true;
        }

        $query = DB::table($table)->where('id', $id);
        $this->applyOwnerScope($query, $table, $userId);

        return $query->exists();
    }

    /**
     * Проверяет владельца всех родительских ссылок из payload при вставке (задача 3.10):
     * нельзя создать запись внутри чужой специализации/заказа/категории.
     */
    private function payloadParentsBelongToUser(string $table, array $payload, ?int $userId): bool
    {
        if ($userId === null) {
            return true;
        }

        foreach (self::OWNER_REFS[$table] ?? [] as $fk => $parent) {
            if (!array_key_exists($fk, $payload) || $payload[$fk] === null) {
                continue;
            }

            if (!$this->ownedIds($parent, $userId)->where('id', $payload[$fk])->exists()) {
                return false;
            }
        }

        return true;
    }

    private function tableHasSoftDeletes(string $table): bool
    {
        // Схема — источник истины (задача 3.9): раньше список был захардкожен
        // (`clients, products, services, categories`) и не знал про `orders`,
        // `equipment_models` и `order_service` — их удаления уходили в hard-delete
        // и не «доезжали» до других устройств.
        return Schema::hasColumn($table, 'deleted_at');
    }

    /**
     * Запоминает факт физического удаления строки (задача 3.9), чтобы его
     * увидели другие устройства через `/sync-updates`.
     *
     * Идемпотентно: повторный DELETE не создаёт второй tombstone (unique
     * `table_name + record_id`), а лишь обновляет время/автора.
     */
    private function recordTombstone(string $table, $recordId, ?string $uuidId, Carbon $moment, ?string $syncId, ?int $userId = null): void
    {
        if ($recordId === null || !Schema::hasTable('sync_tombstones')) {
            return;
        }

        DB::table('sync_tombstones')->updateOrInsert(
            ['table_name' => $table, 'record_id' => $recordId],
            [
                'uuid_id'      => $uuidId,
                'user_id'      => $userId,
                'deleted_at'   => $moment,
                'last_sync_id' => $syncId,
                'updated_at'   => $moment,
                'created_at'   => $moment,
            ]
        );
    }
}
