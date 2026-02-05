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

    // ==============================================
    // 1️⃣ POST /api/sync — отправка операций клиента
    // ==============================================
    public function sync(Request $request)
    {
        $operations = $request->input('operations', []);
        $results = [
            'synced' => [],
            'server_updates' => [],
            'errors' => [],
        ];

        // 1. Получаем ID клиента/сессии. Он должен передаваться в заголовке или теле запроса.
        // Например, 'X-Sync-ID'.
        $syncId = $request->header('X-Sync-ID');

        // Оборачиваем все операции в транзакцию для атомарности
        DB::transaction(function () use ($operations, &$results, $syncId) {
            foreach ($operations as $index => $op) {
                $table   = $op['table'] ?? null;
                $type    = $op['type'] ?? null;
                $payload = $op['payload'] ?? null;
                $localId = $payload['local_id'] ?? $op['id'] ?? null; // Получаем local_id

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
                    $results['errors'][] = [
                        'local_id' => $localId,
                        'error' => 'DATABASE_ERROR',
                        'details' => [
                            'message' => $e->getMessage(),
                            'sql' => $e->getSql(),
                            'bindings' => $e->getBindings(),
                        ],
                    ];
                    Log::error('Sync DB operation failed', ['operation' => $op, 'exception' => $e]);
                } catch (\Exception $e) {
                    $results['errors'][] = ['local_id' => $localId, 'error' => 'GENERAL_ERROR', 'details' => ['message' => $e->getMessage()]];
                    Log::error('Sync operation failed', ['operation' => $op, 'exception' => $e]);
                }
            }
        });

        return response()->json($results);
    }

    // ==============================================
    // 2️⃣ GET /api/sync-updates — получение обновлений для клиента
    // ==============================================
    public function fetchUpdates(Request $request)
    {
        // Жёсткий и честный лог, чтобы видеть реальность
        Log::debug('SYNC_UPDATES REQUEST', ['query' => $request->query()]);

        // Получаем ID клиента, чтобы не отправлять ему его же изменения
        $syncId = $request->header('X-Sync-ID');

        $table = $request->query('table');
        $since = $request->query('since');

        // Проверка table
        if (!$table || !in_array($table, $this->tables, true)) {
            return response()->json([
                'error' => 'Invalid or missing table',
                'received_table' => $table,
                'allowed_tables' => $this->tables,
                'query' => $request->query(),
            ], 400);
        }

        $query = DB::table($table);

        // since ожидаем в миллисекундах
        if ($since !== null && $since !== '') {
            try {
                $sinceCarbon = Carbon::createFromTimestampMs((int)$since);
                $query->where('updated_at', '>', $sinceCarbon);
            } catch (\Throwable $e) {
                return response()->json([
                    'error' => 'Invalid since timestamp',
                    'since' => $since,
                ], 400);
            }
        }

        // 2. Исключаем записи, измененные этим же клиентом
        // Мы проверяем, что колонка существует и что syncId был передан.
        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $query->where(fn($q) => $q->where('last_sync_id', '!=', $syncId)->orWhereNull('last_sync_id'));
        }

        // soft delete — только если колонка есть
        if ($this->tableHasSoftDeletes($table)) {
            $query->whereNull('deleted_at');
        }

        $records = $query
            ->orderBy('updated_at')
            ->get();

        return response()->json([
            'table'   => $table,
            'count'   => $records->count(),
            'records' => $records,
        ]);
    }

    // ==============================================
    // 3️⃣ Вспомогательные методы для sync()
    // ==============================================

    private function insertRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
    {
        // Убираем 'id' и 'local_id', БД должна генерировать 'id' сама
        unset($payload['id'], $payload['local_id']);

        $now = Carbon::now();
        if ($syncId && Schema::hasColumn($table, 'last_sync_id')) {
            $payload['last_sync_id'] = $syncId;
        }
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        // Используем стандартный insert
        DB::table($table)->insert($payload);
        // Получаем ID последней вставленной записи
        $newId = DB::getPdo()->lastInsertId();


        $results['synced'][] = [
            'type' => 'insert',
            'local_id' => $localId, // local_id из операции клиента
            'server_id' => $newId,
        ];
    }

    private function updateRecord(string $table, array $payload, array &$results, ?string $syncId, ?string $localId): void
    {
        $id = $payload['id'];
        unset($payload['id']);

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
        $id = $payload['id'];
        $query = DB::table($table)->where('id', $id);

        $updateData = [];
        if ($this->tableHasSoftDeletes($table)) {
            $updateData['deleted_at'] = Carbon::now();
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
        $softDeleteTables = ['orders', 'clients', 'products'];
        return in_array($table, $softDeleteTables, true);
    }
}
