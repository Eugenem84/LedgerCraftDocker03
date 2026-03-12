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
        'service_categories',
        'by_product_prices',
        'sales_product_prices',
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
                } catch (QueryException $e) {
                    $results['errors'][] = ['local_id' => $localId, 'error' => 'DATABASE_ERROR', 'details' => ['message' => $e->getMessage(), 'sql' => $e->getSql(), 'bindings' => $e->getBindings()]];
                    Log::error('Sync DB operation failed', ['operation' => $op, 'exception' => $e]);
                } catch (\Exception $e) {
                    $results['errors'][] = ['local_id' => $localId, 'error' => 'GENERAL_ERROR', 'details' => ['message' => $e->getMessage()]];
                    Log::error('Sync operation failed', ['operation' => $op, 'exception' => $e]);
                }
            }
        });

        return response()->json($results);
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
        return response()->json(['table' => $table, 'count' => $records->count(), 'records' => $records]);
    }

    private function insertRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
    {
        // Служебные поля, которые не должны попадать в реальные колонки таблиц
        unset($payload['id'], $payload['local_id'], $payload['uuid_id']);

        $now = Carbon::now();

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

            $newId = DB::table($table)->insertGetId($data);

            $results['synced'][] = [
                'type'      => 'insert',
                'local_id'  => $localId,
                'server_id' => $newId,
            ];

            return;
        }

        // Специальная обработка для order_service:
        // на сервере это чисто связывающая таблица без PK и timestamps
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

            $now = Carbon::now();

            $data = [
                'order_id'    => $orderId,
                'service_id'  => $serviceId,
                'sale_price'  => $salePrice,
                'quantity'    => $quantity,
                'created_at'  => $now,
                'updated_at'  => $now,
                // uuid_* и deleted_at заполняться не будут — по договорённости их игнорируем
            ];

            // #region agent log
            $logPayload = [
                'sessionId'    => 'c685cd',
                'runId'        => 'pre-fix',
                'hypothesisId' => 'H3',
                'location'     => 'SyncController.php:insertRecord:order_service',
                'message'      => 'insertRecord for order_service',
                'data'         => [
                    'payload' => $payload,
                    'data'    => $data,
                ],
                'timestamp'    => round(microtime(true) * 1000),
            ];
            @file_put_contents(
                '/Users/artem/PhpstormProjects/ledger-craft-offline-first-PS/.cursor/debug-c685cd.log',
                json_encode($logPayload, JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );
            // #endregion agent log

            DB::table($table)->insert($data);

            // Для связки серверный ID нам не нужен, но для единообразия вернём null
            $results['synced'][] = [
                'type'      => 'insert',
                'local_id'  => $localId,
                'server_id' => null,
            ];

            return;
        }

        // Обработка по умолчанию для остальных таблиц
        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
        }
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $newId = DB::table($table)->insertGetId($payload);

        $results['synced'][] = [
            'type'      => 'insert',
            'local_id'  => $localId,
            'server_id' => $newId,
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
        unset($payload['id'], $payload['local_id'], $payload['uuid_id']);

        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
        }
        $payload['updated_at'] = Carbon::now();

        $affected = DB::table($table)->where('id', $id)->update($payload);

        if ($affected > 0) {
            $results['synced'][] = [
                'type' => 'update',
                'local_id' => $localId,
                'server_id' => $id,
            ];
        }
    }

    private function deleteRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
    {
        if (!array_key_exists('id', $payload)) {
            $results['errors'][] = [
                'local_id' => $localId,
                'error' => 'MISSING_ID_FOR_DELETE',
            ];
            return;
        }

        $id = $payload['id'];
        $query = DB::table($table)->where('id', $id);

        $affected = 0;
        if ($this->tableHasSoftDeletes($table)) {
            $updateData = ['deleted_at' => Carbon::now()];
            if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
                $updateData['last_sync_id'] = $syncId;
            }
            $affected = $query->update($updateData);
        } else {
            $affected = $query->delete();
        }

        if ($affected > 0) {
            $results['synced'][] = [
                'type' => 'delete',
                'local_id' => $localId,
                'server_id' => $id,
            ];
        }
    }

    private function tableHasSoftDeletes(string $table): bool
    {
        // Важно: у таблицы orders в миграции нет deleted_at,
        // поэтому здесь перечисляем только реально soft-deletable таблицы.
        $softDeleteTables = ['clients', 'products', 'services', 'categories'];
        return in_array($table, $softDeleteTables, true);
    }
}
