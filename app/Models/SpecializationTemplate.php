<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Пресет специализации (Фаза 10, задача 10.7, решение D5).
 *
 * Сервер — источник пресетов: `content` (JSON) можно поправить без релиза
 * приложения, а клиент держит read-only кэш и офлайн работает из него.
 */
class SpecializationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'preset_key',
        'version',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'version' => 'integer',
    ];
}
