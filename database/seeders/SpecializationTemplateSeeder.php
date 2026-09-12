<?php

namespace Database\Seeders;

use App\Models\SpecializationTemplate;
use Illuminate\Database\Seeder;

/**
 * Сид пресетов специализаций (Фаза 11, задача 11.3; решения D4/D5, задача 10.7).
 *
 * Пресет — это **контент**, а не данные пользователя: сервер держит его в
 * `specialization_templates`, клиент забирает через `GET /api/specialization-templates`
 * и кэширует в `meta`. Поэтому поправить каталог (категории → услуги с ценами,
 * категории товаров, модели) можно без релиза приложения — достаточно
 * отредактировать этот файл и перезапустить сид:
 *
 *     php artisan db:seed --class=SpecializationTemplateSeeder --force
 *
 * `content` повторяет формат клиентских пресетов (`src/domain/presets/*.js`), но
 * только каталог: метаданные UI (лексикон, акцент, флаги вкладок) остаются на
 * клиенте — от них зависит представление, и они не должны уезжать с контентом
 * (`src/services/presetService.js`, `mergePreset()`).
 *
 * Идемпотентность (`updateOrCreate` по `preset_key`) — по решению D5: повторный
 * запуск не плодит дубли, а освежает контент до версии из репозитория. Правило
 * «правка контента без релиза клиента» проверяется тестом
 * `tests/Feature/SpecializationTemplateSeederTest.php`.
 */
class SpecializationTemplateSeeder extends Seeder
{
    /**
     * Идемпотентно создаёт/обновляет 4 пресета ниш v1 (решение D5):
     * `bike`, `aquarium`, `hvac`, `auto`.
     */
    public function run(): void
    {
        foreach ($this->presets() as $preset) {
            SpecializationTemplate::updateOrCreate(
                ['preset_key' => $preset['preset_key']],
                [
                    'version' => $preset['version'],
                    'content' => $preset['content'],
                ],
            );
        }
    }

    /**
     * Контент пресетов. Формат — как у клиентских пресетов из `src/domain/presets`.
     *
     * @return array<int, array{preset_key: string, version: int, content: array<string, mixed>}>
     */
    private function presets(): array
    {
        return [
            [
                'preset_key' => 'bike',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'wheels',
                            'name' => 'Колёса',
                            'services' => [
                                ['name' => 'Замена камеры', 'price' => 400],
                                ['name' => 'Замена покрышки', 'price' => 500],
                                ['name' => 'Правка обода', 'price' => 900],
                                ['name' => 'Сборка колеса', 'price' => 2000],
                            ],
                        ],
                        [
                            'key' => 'brakes',
                            'name' => 'Тормоза',
                            'services' => [
                                ['name' => 'Замена колодок', 'price' => 600],
                                ['name' => 'Регулировка тормозов', 'price' => 500],
                                ['name' => 'Прокачка гидравлики', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'drivetrain',
                            'name' => 'Трансмиссия',
                            'services' => [
                                ['name' => 'Замена цепи', 'price' => 500],
                                ['name' => 'Замена кассеты', 'price' => 1200],
                                ['name' => 'Регулировка переключения', 'price' => 700],
                                ['name' => 'Замена троса', 'price' => 600],
                            ],
                        ],
                        [
                            'key' => 'service',
                            'name' => 'Обслуживание',
                            'services' => [
                                ['name' => 'Полное ТО', 'price' => 3500],
                                ['name' => 'Чистка и смазка', 'price' => 1500],
                                ['name' => 'Замена тросиков и рубашек', 'price' => 1800],
                            ],
                        ],
                        [
                            'key' => 'fork',
                            'name' => 'Рулевая и вилка',
                            'services' => [
                                ['name' => 'Замена вилки', 'price' => 2500],
                                ['name' => 'Регулировка рулевой', 'price' => 800],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'spares', 'name' => 'Запчасти'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                        ['key' => 'accessories', 'name' => 'Аксессуары'],
                    ],
                    'models' => [
                        ['key' => 'mtb', 'name' => 'Горный (MTB)'],
                        ['key' => 'road', 'name' => 'Шоссейный'],
                        ['key' => 'city', 'name' => 'Городской'],
                        ['key' => 'bmx', 'name' => 'BMX'],
                        ['key' => 'e-bike', 'name' => 'Электровелосипед'],
                    ],
                ],
            ],
            [
                'preset_key' => 'aquarium',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'maintenance',
                            'name' => 'Чистка и обслуживание',
                            'services' => [
                                ['name' => 'Чистка аквариума', 'price' => 1500],
                                ['name' => 'Замена воды', 'price' => 800],
                                ['name' => 'Чистка фильтра', 'price' => 700],
                            ],
                        ],
                        [
                            'key' => 'water',
                            'name' => 'Вода и тесты',
                            'services' => [
                                ['name' => 'Тест воды', 'price' => 400],
                                ['name' => 'Коррекция pH', 'price' => 600],
                                ['name' => 'Восстановление азотного цикла', 'price' => 1200],
                            ],
                        ],
                        [
                            'key' => 'equipment',
                            'name' => 'Оборудование',
                            'services' => [
                                ['name' => 'Установка фильтра', 'price' => 1200],
                                ['name' => 'Установка освещения', 'price' => 1500],
                                ['name' => 'Замена ламп', 'price' => 500],
                            ],
                        ],
                        [
                            'key' => 'setup',
                            'name' => 'Запуск и заселение',
                            'services' => [
                                ['name' => 'Запуск аквариума', 'price' => 3000],
                                ['name' => 'Акваскейп', 'price' => 5000],
                                ['name' => 'Заселение рыбой', 'price' => 1000],
                            ],
                        ],
                        [
                            'key' => 'treatment',
                            'name' => 'Лечение',
                            'services' => [
                                ['name' => 'Диагностика болезней', 'price' => 800],
                                ['name' => 'Лечение ихтиофтириоза', 'price' => 1000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'food', 'name' => 'Корма'],
                        ['key' => 'equipment-goods', 'name' => 'Оборудование'],
                        ['key' => 'chemistry', 'name' => 'Химия'],
                        ['key' => 'accessories', 'name' => 'Аксессуары'],
                    ],
                    'models' => [
                        ['key' => 'nano', 'name' => 'Нано 30 л'],
                        ['key' => 'planted', 'name' => 'Травник 100 л'],
                        ['key' => 'cichlid', 'name' => 'Цихлидник 150 л'],
                        ['key' => 'marine', 'name' => 'Морской 200 л'],
                    ],
                ],
            ],
            [
                'preset_key' => 'hvac',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'install',
                            'name' => 'Монтаж',
                            'services' => [
                                ['name' => 'Монтаж кондиционера', 'price' => 8000],
                                ['name' => 'Демонтаж кондиционера', 'price' => 3000],
                                ['name' => 'Прокладка трассы', 'price' => 2500],
                            ],
                        ],
                        [
                            'key' => 'maintenance',
                            'name' => 'Обслуживание',
                            'services' => [
                                ['name' => 'Чистка кондиционера', 'price' => 2500],
                                ['name' => 'Дозаправка фреона', 'price' => 3000],
                                ['name' => 'Проверка давления', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'repair',
                            'name' => 'Ремонт',
                            'services' => [
                                ['name' => 'Поиск утечки', 'price' => 2000],
                                ['name' => 'Замена компрессора', 'price' => 7000],
                                ['name' => 'Ремонт платы управления', 'price' => 3500],
                            ],
                        ],
                        [
                            'key' => 'diagnostics',
                            'name' => 'Диагностика',
                            'services' => [
                                ['name' => 'Диагностика неисправности', 'price' => 1500],
                                ['name' => 'Замер параметров', 'price' => 1000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'spares', 'name' => 'Запчасти'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                        ['key' => 'install-materials', 'name' => 'Материалы монтажа'],
                    ],
                    'models' => [
                        ['key' => 'wall-split', 'name' => 'Настенный сплит'],
                        ['key' => 'multi-split', 'name' => 'Мульти-сплит'],
                        ['key' => 'duct', 'name' => 'Канальный'],
                        ['key' => 'vrf', 'name' => 'Мультизональный VRF'],
                    ],
                ],
            ],
            [
                'preset_key' => 'auto',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'to',
                            'name' => 'ТО',
                            'services' => [
                                ['name' => 'Замена масла', 'price' => 1500],
                                ['name' => 'Замена фильтров', 'price' => 1200],
                                ['name' => 'Диагностика ходовой', 'price' => 1000],
                            ],
                        ],
                        [
                            'key' => 'brakes',
                            'name' => 'Тормозная система',
                            'services' => [
                                ['name' => 'Замена колодок', 'price' => 2500],
                                ['name' => 'Замена дисков', 'price' => 4000],
                                ['name' => 'Прокачка тормозов', 'price' => 2000],
                            ],
                        ],
                        [
                            'key' => 'engine',
                            'name' => 'Двигатель',
                            'services' => [
                                ['name' => 'Замена ремня ГРМ', 'price' => 8000],
                                ['name' => 'Замена свечей', 'price' => 2000],
                                ['name' => 'Промывка форсунок', 'price' => 3500],
                            ],
                        ],
                        [
                            'key' => 'electric',
                            'name' => 'Электрика',
                            'services' => [
                                ['name' => 'Диагностика электрики', 'price' => 1500],
                                ['name' => 'Замена аккумулятора', 'price' => 500],
                                ['name' => 'Установка сигнализации', 'price' => 5000],
                            ],
                        ],
                        [
                            'key' => 'geometry',
                            'name' => 'Развал-схождение',
                            'services' => [
                                ['name' => 'Развал-схождение', 'price' => 3000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'spares', 'name' => 'Запчасти'],
                        ['key' => 'fluids', 'name' => 'Масла и жидкости'],
                        ['key' => 'chemistry', 'name' => 'Автохимия'],
                        ['key' => 'tires', 'name' => 'Шины и диски'],
                    ],
                    'models' => [
                        ['key' => 'sedan', 'name' => 'Седан'],
                        ['key' => 'hatchback', 'name' => 'Хэтчбек'],
                        ['key' => 'crossover', 'name' => 'Кроссовер'],
                        ['key' => 'suv', 'name' => 'Внедорожник'],
                        ['key' => 'minivan', 'name' => 'Минивэн'],
                    ],
                ],
            ],
        ];
    }
}
