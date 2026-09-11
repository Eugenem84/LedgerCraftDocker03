<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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

    public function sync(Request $request)
    {
        $operations = $request->input('operations', []);
        $results = [
            'synced' => [],
            'errors' => [],
        ];
        $syncId = $request->header('X-Sync-ID');

        DB::transaction(function () use ($operations, &$results, $syncId) {
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
                            $this->insertRecord($table, $payload, $results, $syncId, $localId);
                            break;
                        case 'update':
                            $this->updateRecord($table, $payload, $results, $syncId, $localId);
                            break;
                        case 'delete':
                            $this->deleteRecord($table, $payload, $results, $syncId, $localId);
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

        if ($this->tableHasSoftDeletes($table)) {
            $query->whereNull('deleted_at');
        }

        $records = $query->orderBy('updated_at')->get();

        // Единый стандарт времени (задача 3.8): сервер отдаёт timestamps ISO-8601 UTC
        // (`2026-09-12T10:00:00.000000Z`). «Сырое» значение Postgres (`2026-09-12 10:00:00`)
        // клиентский `Date.parse` принимает за ЛОКАЛЬНОЕ время устройства — сравнение версий
        // (last-write-wins) и курсор выдачи смещались бы на часовой пояс.
        $records->each(fn ($record) => $this->normalizeTimestamps($record));

        return response()->json(['table' => $table, 'count' => $records->count(), 'records' => $records]);
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

    private function insertRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
    {
        // Служебные поля клиента не должны попадать в реальные колонки таблиц:
        // `id`/`local_id`/`uuid_id`, а также `server_id` и сигнальные `*_server_id`
        // (вырезание перенесено из Go-сайдкара, D1/задача 3.11).
        $payload = $this->stripClientFields($payload);

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
                // materials сейчас не синкаем с клиента, пусть будет NULL
            ];

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

        // Обработка по умолчанию для остальных таблиц
        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
        }
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $newId = $this->upsertRecord($table, $payload, $localId);

        $results['synced'][] = [
            'type'       => 'insert',
            'local_id'   => $localId,
            'server_id'  => $newId,
            'updated_at' => $now->toJSON(),
        ];
    }

    private function updateRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
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
        $payload = $this->stripClientFields($payload);

        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
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

    private function deleteRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
    {
        $id = $payload['id'] ?? null;

        // Время операции: у soft-delete это ещё и новая версия записи (задача 3.8).
        $now = $this->syncNow();

        // order_service — связка без собственного PK: удаляем по натуральному
        // ключу `order_id + service_id` (серверные id), который присылает клиент.
        if ($id === null && $table === 'order_service') {
            $orderId   = $payload['order_id'] ?? null;
            $serviceId = $payload['service_id'] ?? null;

            if ($orderId !== null && $serviceId !== null) {
                $affected = DB::table($table)
                    ->where('order_id', $orderId)
                    ->where('service_id', $serviceId)
                    ->delete();

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
            $query->delete();
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

            DB::table($table)->where('uuid_id', $localId)->update($update);

            return $existing->id ?? null;
        }

        $data['uuid_id'] = $localId;

        return DB::table($table)->insertGetId($data);
    }

    /**
     * Вырезает служебные поля клиента из payload: `id`, `local_id`, `uuid_id`,
     * `server_id` и сигнальные `*_server_id`. Перенос из Go-сайдкара (D1, 3.11):
     * иначе `server_id`/`*_server_id` улетали бы в реальные колонки таблиц.
     */
    private function stripClientFields(array $payload): array
    {
        $clean = [];

        foreach ($payload as $key => $value) {
            if (in_array($key, ['id', 'local_id', 'uuid_id', 'server_id'], true)) {
                continue;
            }
            if (str_ends_with($key, '_server_id')) {
                continue;
            }
            $clean[$key] = $value;
        }

        return $clean;
    }

    private function tableHasSoftDeletes(string $table): bool
    {
        // Важно: у таблицы orders в миграции нет deleted_at,
        // поэтому здесь перечисляем только реально soft-deletable таблицы.
        $softDeleteTables = ['clients', 'products', 'services', 'categories'];
        return in_array($table, $softDeleteTables, true);
    }
}
