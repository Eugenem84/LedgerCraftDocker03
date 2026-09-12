<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Поля рабочего профиля специализации (Фаза 10, задача 10.6, решения D4/D5).
 *
 * «Специализация» — это рабочий профиль мастерской (оригинальное имя колонки —
 * `specializationName`), к ней привязаны заказы, клиенты и каталог. Чтобы профиль
 * «говорил на языке» ниши, ему нужны метаданные UI, которые обязаны ходить через
 * синк (иначе не переживут `fullReset` и не приедут на второе устройство):
 *   • preset_key       — какой пресет применён (`bike` / `aquarium` / `hvac` / `auto`);
 *   • accent           — акцентный цвет (клиент применяет через `setCssVar`);
 *   • features         — JSON-флаги видимости вкладок/блоков (склад, модели, аналитика);
 *   • archived         — архив вместо физического удаления (каскадный FK снёс бы каталог);
 *   • template_version — версия применённого пресета.
 *
 * Все колонки nullable: у старых записей пресета просто нет — UI остаётся прежним.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specializations', function (Blueprint $table) {
            $table->string('preset_key')->nullable()->after('user_id');
            $table->string('accent')->nullable()->after('preset_key');
            $table->text('features')->nullable()->after('accent');
            $table->boolean('archived')->default(false)->after('features');
            $table->unsignedInteger('template_version')->nullable()->after('archived');
        });
    }

    public function down(): void
    {
        Schema::table('specializations', function (Blueprint $table) {
            $table->dropColumn(['preset_key', 'accent', 'features', 'archived', 'template_version']);
        });
    }
};
