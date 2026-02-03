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
    $table = $request->query('table');
    //$table = 'specializations';
    $since = $request->query('since', 0);

    // временно
if (empty($table)) {
    return response()->json([
        'error' => 'Missing table',
        'request' => $request,
    ]);
}

    // если table пустой или недопустимый — вернём пустой массив
    if (empty($table) || !in_array($table, $this->tables)) {
        return response()->json([
            'error' => 'Invalid or missing table parameter',
            'table' => $table,
            'request' => $request->query(),
        ]);
    }

    $query = DB::table($table);

    // Добавляем условие для "мягкого удаления" только если колонка существует в таблице
    if (Schema::hasColumn($table, 'deleted_at')) {
        $query->whereNull('deleted_at');
    }

    $updates = $query->get();

    // отдаём прямо в браузер: количество + первые 10 записей
    return response()->json([
        'count'   => $updates->count(),
        'records' => $updates->take(10),
    ]);
}

    // ==============================================
    // INSERT
    // ==============================================
    private function insertRecord($table, $payload, &$synced)
    {
        $clientId = $payload['id'] ?? null;
        $updatedAt = Carbon::createFromTimestamp($payload['updated_at']);

        unset($payload['id'], $payload['server_id']);
        $payload['created_at'] = $updatedAt;
        $payload['updated_at'] = $updatedAt;

        $serverId = DB::table($table)->insertGetId($payload);

        $synced[] = [
            'table'     => $table,
            'local_id'  => $clientId,
            'server_id' => $serverId,
        ];
    }

    // ==============================================
    // UPDATE
    // ==============================================
    private function updateRecord($table, $payload, &$synced, &$serverUpdates)
    {
        if (empty($payload['server_id'])) return;

        $server = DB::table($table)->where('id', $payload['server_id'])->first();
        if (!$server) return;

        $clientUpdatedAt = Carbon::createFromTimestamp($payload['updated_at']);

        if (Carbon::parse($server->updated_at) < $clientUpdatedAt) {
            unset($payload['id'], $payload['server_id']);
            $payload['updated_at'] = $clientUpdatedAt;
            DB::table($table)->where('id', $server->id)->update((array)$payload);
        } else {
            $serverArr = (array)$server;
            $serverArr['updated_at'] = Carbon::parse($server->updated_at)->timestamp;
            $serverUpdates[] = [
                'table'     => $table,
                'server_id' => $server->id,
                'record'    => $serverArr,
            ];
        }

        $synced[] = [
            'table'     => $table,
            'local_id'  => $payload['id'] ?? null,
            'server_id' => $payload['server_id'],
        ];
    }

    // ==============================================
    // DELETE
    // ==============================================
    private function deleteRecord($table, $payload, &$synced)
    {
        if (empty($payload['server_id'])) return;

        DB::table($table)
            ->where('id', $payload['server_id'])
            ->update(['deleted_at' => now()]);

        $synced[] = [
            'table'     => $table,
            'local_id'  => $payload['id'] ?? null,
            'server_id' => $payload['server_id'],
        ];
    }
}
