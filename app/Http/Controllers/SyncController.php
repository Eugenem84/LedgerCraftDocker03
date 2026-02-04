<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    private array $tables = [
        'clients',
        'specializations',
        // 'orders', 'invoices', ...
    ];

    // ==============================================
    // 1️⃣ POST /api/sync — отправка операций клиента
    // ==============================================
    public function sync(Request $request)
    {
        $operations = $request->input('operations', []);
        $synced = [];
        $serverUpdates = [];

        foreach ($operations as $op) {
            $table   = $op['table'];
            $type    = $op['type'];
            $payload = $op['payload'];

            if (!in_array($table, $this->tables)) continue;

            match ($type) {
                'insert' => $this->insertRecord($table, $payload, $synced),
                'update' => $this->updateRecord($table, $payload, $synced, $serverUpdates),
                'delete' => $this->deleteRecord($table, $payload, $synced),
                default  => null,
            };
        }

        return response()->json([
            'synced'         => $synced,
            'server_updates' => $serverUpdates,
        ]);
    }

    // ==============================================
    // 2️⃣ GET /api/sync-updates — получение обновлений для клиента
    // ==============================================
    public function fetchUpdates(Request $request)
    {
        // Жёсткий и честный лог, чтобы видеть реальность
        Log::debug('SYNC_UPDATES REQUEST', [
            'method'   => $request->method(),
            'full_url' => $request->fullUrl(),
            'query'    => $request->query(),
            '_GET'     => $_GET,
        ]);

        // GET → ТОЛЬКО query
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

        // soft delete — только если колонка есть
        if (Schema::hasColumn($table, 'deleted_at')) {
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
    }}
