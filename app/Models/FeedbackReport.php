<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Отчёт «Сообщить об ошибке» (Фаза 14, задачи 14.4/14.6).
 *
 * Данные мастерской (заказы, клиенты, суммы) сюда не попадают: клиент собирает отчёт
 * поле за полем (`src/utils/feedbackView.js`), а сервер принимает только известные поля
 * (`FeedbackController::store`). `payload` хранит отчёт целиком — он отдаётся ручкой
 * выгрузки и попадает в инбокс `feedback/INBOX.md` (задача 14.7).
 */
class FeedbackReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid_id',
        'user_id',
        'kind',
        'message',
        'contact',
        'screen',
        'app_version',
        'platform',
        'platform_version',
        'device',
        'api_url',
        'schema_version',
        'schema_stored',
        'account_email',
        'profile_name',
        'payload',
        'sync',
        'errors',
        'logs',
        'client_created_at',
        'status',
        'resolution_note',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'payload'           => 'array',
        'sync'              => 'array',
        'errors'            => 'array',
        'logs'              => 'array',
        'client_created_at' => 'datetime',
        'schema_version'    => 'integer',
        'schema_stored'     => 'integer',
    ];
}
