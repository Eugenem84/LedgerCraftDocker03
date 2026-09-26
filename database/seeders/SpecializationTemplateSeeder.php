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
     * Идемпотентно создаёт/обновляет пресеты ниш: четыре v1 (решение D5) —
     * `bike`, `aquarium`, `hvac`, `auto` — и расширение реестра для массовых
     * офлайн-мастеров — `electric`, `plumbing`, `appliance`, `phone`, `computer`,
     * `furniture`, `windows`, `cleaning`. Итого 12 пресетов.
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
            [
                'preset_key' => 'electric',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'sockets',
                            'name' => 'Розетки и выключатели',
                            'services' => [
                                ['name' => 'Установка розетки', 'price' => 600],
                                ['name' => 'Замена выключателя', 'price' => 500],
                                ['name' => 'Перенос розетки', 'price' => 900],
                            ],
                        ],
                        [
                            'key' => 'panel',
                            'name' => 'Щиток',
                            'services' => [
                                ['name' => 'Сборка щитка', 'price' => 3500],
                                ['name' => 'Установка автомата', 'price' => 500],
                                ['name' => 'Установка УЗО', 'price' => 900],
                            ],
                        ],
                        [
                            'key' => 'wiring',
                            'name' => 'Проводка',
                            'services' => [
                                ['name' => 'Прокладка кабеля', 'price' => 300],
                                ['name' => 'Замена проводки (точка)', 'price' => 1200],
                                ['name' => 'Штробление стены', 'price' => 400],
                            ],
                        ],
                        [
                            'key' => 'lighting',
                            'name' => 'Освещение',
                            'services' => [
                                ['name' => 'Установка люстры', 'price' => 1200],
                                ['name' => 'Установка точечного светильника', 'price' => 500],
                                ['name' => 'Монтаж LED-ленты', 'price' => 800],
                            ],
                        ],
                        [
                            'key' => 'faults',
                            'name' => 'Поиск неисправностей',
                            'services' => [
                                ['name' => 'Диагностика проводки', 'price' => 1500],
                                ['name' => 'Поиск обрыва', 'price' => 2000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'cable', 'name' => 'Кабель и провода'],
                        ['key' => 'breakers', 'name' => 'Автоматы и УЗО'],
                        ['key' => 'sockets-goods', 'name' => 'Розетки и выключатели'],
                        ['key' => 'lights', 'name' => 'Светильники'],
                    ],
                    'models' => [
                        ['key' => 'apartment', 'name' => 'Квартира'],
                        ['key' => 'house', 'name' => 'Частный дом'],
                        ['key' => 'office', 'name' => 'Офис'],
                        ['key' => 'garage', 'name' => 'Гараж'],
                    ],
                ],
            ],
            [
                'preset_key' => 'plumbing',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'mixers',
                            'name' => 'Смесители и краны',
                            'services' => [
                                ['name' => 'Замена смесителя', 'price' => 1200],
                                ['name' => 'Замена картриджа смесителя', 'price' => 800],
                                ['name' => 'Установка шарового крана', 'price' => 700],
                                ['name' => 'Устранение течи', 'price' => 900],
                            ],
                        ],
                        [
                            'key' => 'toilets',
                            'name' => 'Унитазы и ванны',
                            'services' => [
                                ['name' => 'Установка унитаза', 'price' => 3500],
                                ['name' => 'Замена бачка', 'price' => 2000],
                                ['name' => 'Установка ванны', 'price' => 6000],
                                ['name' => 'Замена сифона', 'price' => 900],
                            ],
                        ],
                        [
                            'key' => 'pipes',
                            'name' => 'Трубы и разводка',
                            'services' => [
                                ['name' => 'Замена участка трубы', 'price' => 1500],
                                ['name' => 'Разводка труб (точка)', 'price' => 2500],
                                ['name' => 'Установка фильтра воды', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'clogs',
                            'name' => 'Прочистка засоров',
                            'services' => [
                                ['name' => 'Прочистка засора', 'price' => 2000],
                                ['name' => 'Гидродинамическая прочистка', 'price' => 4500],
                            ],
                        ],
                        [
                            'key' => 'boilers',
                            'name' => 'Водонагреватели',
                            'services' => [
                                ['name' => 'Установка бойлера', 'price' => 4000],
                                ['name' => 'Замена ТЭНа', 'price' => 2500],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'pipes-goods', 'name' => 'Трубы и фитинги'],
                        ['key' => 'mixers-goods', 'name' => 'Смесители и краны'],
                        ['key' => 'sanitary', 'name' => 'Санфаянс'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                    ],
                    'models' => [
                        ['key' => 'apartment', 'name' => 'Квартира'],
                        ['key' => 'house', 'name' => 'Частный дом'],
                        ['key' => 'office', 'name' => 'Офис'],
                        ['key' => 'country', 'name' => 'Дача'],
                    ],
                ],
            ],
            [
                'preset_key' => 'appliance',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'washing',
                            'name' => 'Стиральные машины',
                            'services' => [
                                ['name' => 'Диагностика', 'price' => 800],
                                ['name' => 'Замена подшипника', 'price' => 4500],
                                ['name' => 'Замена насоса', 'price' => 2500],
                                ['name' => 'Чистка фильтра', 'price' => 1000],
                                ['name' => 'Замена ремня', 'price' => 1800],
                            ],
                        ],
                        [
                            'key' => 'fridges',
                            'name' => 'Холодильники',
                            'services' => [
                                ['name' => 'Замена компрессора', 'price' => 6500],
                                ['name' => 'Заправка фреоном', 'price' => 4000],
                                ['name' => 'Замена термостата', 'price' => 2500],
                                ['name' => 'Устранение утечки', 'price' => 3500],
                            ],
                        ],
                        [
                            'key' => 'dishwashers',
                            'name' => 'Посудомоечные машины',
                            'services' => [
                                ['name' => 'Замена помпы', 'price' => 2800],
                                ['name' => 'Прочистка форсунок', 'price' => 2000],
                                ['name' => 'Замена уплотнителя', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'stoves',
                            'name' => 'Плиты и духовки',
                            'services' => [
                                ['name' => 'Замена нагревателя', 'price' => 2200],
                                ['name' => 'Замена термопары', 'price' => 1800],
                                ['name' => 'Ремонт электроники', 'price' => 3000],
                            ],
                        ],
                        [
                            'key' => 'microwaves',
                            'name' => 'Микроволновки',
                            'services' => [
                                ['name' => 'Замена магнетрона', 'price' => 3200],
                                ['name' => 'Замена слюды', 'price' => 1200],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'spares', 'name' => 'Запчасти'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                        ['key' => 'fasteners', 'name' => 'Крепёж'],
                        ['key' => 'tools', 'name' => 'Инструмент'],
                    ],
                    'models' => [
                        ['key' => 'washer', 'name' => 'Стиральная машина'],
                        ['key' => 'fridge', 'name' => 'Холодильник'],
                        ['key' => 'dishwasher', 'name' => 'Посудомоечная машина'],
                        ['key' => 'stove', 'name' => 'Плита'],
                        ['key' => 'microwave', 'name' => 'Микроволновка'],
                    ],
                ],
            ],
            [
                'preset_key' => 'phone',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'phones',
                            'name' => 'Телефоны',
                            'services' => [
                                ['name' => 'Замена экрана', 'price' => 3500],
                                ['name' => 'Замена аккумулятора', 'price' => 1500],
                                ['name' => 'Замена разъёма зарядки', 'price' => 1800],
                                ['name' => 'Замена камеры', 'price' => 2000],
                            ],
                        ],
                        [
                            'key' => 'laptops',
                            'name' => 'Ноутбуки',
                            'services' => [
                                ['name' => 'Замена клавиатуры', 'price' => 2500],
                                ['name' => 'Замена матрицы', 'price' => 4000],
                                ['name' => 'Чистка от пыли', 'price' => 1800],
                                ['name' => 'Замена термопасты', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'tablets',
                            'name' => 'Планшеты',
                            'services' => [
                                ['name' => 'Замена тачскрина', 'price' => 3000],
                                ['name' => 'Замена аккумулятора', 'price' => 2200],
                            ],
                        ],
                        [
                            'key' => 'software',
                            'name' => 'Программное',
                            'services' => [
                                ['name' => 'Переустановка ПО', 'price' => 1200],
                                ['name' => 'Восстановление данных', 'price' => 2500],
                                ['name' => 'Разблокировка', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'diagnostics',
                            'name' => 'Диагностика',
                            'services' => [
                                ['name' => 'Диагностика', 'price' => 500],
                                ['name' => 'Чистка после влаги', 'price' => 2500],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'screens', 'name' => 'Экраны и дисплеи'],
                        ['key' => 'batteries', 'name' => 'Аккумуляторы'],
                        ['key' => 'connectors', 'name' => 'Разъёмы и шлейфы'],
                        ['key' => 'accessories', 'name' => 'Аксессуары'],
                    ],
                    'models' => [
                        ['key' => 'smartphone', 'name' => 'Смартфон'],
                        ['key' => 'tablet', 'name' => 'Планшет'],
                        ['key' => 'laptop', 'name' => 'Ноутбук'],
                        ['key' => 'smartwatch', 'name' => 'Умные часы'],
                    ],
                ],
            ],
            [
                'preset_key' => 'computer',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'setup',
                            'name' => 'Настройка и ПО',
                            'services' => [
                                ['name' => 'Установка Windows', 'price' => 1500],
                                ['name' => 'Настройка программ', 'price' => 1200],
                                ['name' => 'Удаление вирусов', 'price' => 1500],
                                ['name' => 'Настройка роутера', 'price' => 1200],
                            ],
                        ],
                        [
                            'key' => 'hardware',
                            'name' => 'Железо',
                            'services' => [
                                ['name' => 'Замена SSD', 'price' => 1500],
                                ['name' => 'Установка ОЗУ', 'price' => 1000],
                                ['name' => 'Замена блока питания', 'price' => 1500],
                                ['name' => 'Сборка ПК', 'price' => 3500],
                            ],
                        ],
                        [
                            'key' => 'data',
                            'name' => 'Данные',
                            'services' => [
                                ['name' => 'Восстановление данных', 'price' => 3000],
                                ['name' => 'Перенос данных', 'price' => 1500],
                                ['name' => 'Резервное копирование', 'price' => 1000],
                            ],
                        ],
                        [
                            'key' => 'maintenance',
                            'name' => 'Обслуживание',
                            'services' => [
                                ['name' => 'Чистка от пыли', 'price' => 1800],
                                ['name' => 'Замена термопасты', 'price' => 1500],
                                ['name' => 'Установка охлаждения', 'price' => 2000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'components', 'name' => 'Комплектующие'],
                        ['key' => 'peripherals', 'name' => 'Периферия'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                        ['key' => 'software-goods', 'name' => 'ПО и лицензии'],
                    ],
                    'models' => [
                        ['key' => 'laptop', 'name' => 'Ноутбук'],
                        ['key' => 'desktop', 'name' => 'Системный блок'],
                        ['key' => 'all-in-one', 'name' => 'Моноблок'],
                        ['key' => 'router', 'name' => 'Роутер'],
                    ],
                ],
            ],
            [
                'preset_key' => 'furniture',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'assembly',
                            'name' => 'Сборка',
                            'services' => [
                                ['name' => 'Сборка шкафа', 'price' => 2500],
                                ['name' => 'Сборка кухни', 'price' => 5000],
                                ['name' => 'Сборка кровати', 'price' => 1800],
                                ['name' => 'Сборка стола', 'price' => 1200],
                            ],
                        ],
                        [
                            'key' => 'repair',
                            'name' => 'Ремонт',
                            'services' => [
                                ['name' => 'Замена петель', 'price' => 800],
                                ['name' => 'Замена направляющих', 'price' => 1200],
                                ['name' => 'Ремонт столешницы', 'price' => 2000],
                            ],
                        ],
                        [
                            'key' => 'hanging',
                            'name' => 'Навеска',
                            'services' => [
                                ['name' => 'Навеска шкафа', 'price' => 1500],
                                ['name' => 'Навеска полки', 'price' => 500],
                                ['name' => 'Монтаж зеркала', 'price' => 1000],
                            ],
                        ],
                        [
                            'key' => 'custom',
                            'name' => 'Изготовление',
                            'services' => [
                                ['name' => 'Изготовление полки', 'price' => 2500],
                                ['name' => 'Изготовление стола', 'price' => 8000],
                                ['name' => 'Распил ЛДСП', 'price' => 500],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'fittings', 'name' => 'Фурнитура'],
                        ['key' => 'fasteners', 'name' => 'Крепёж'],
                        ['key' => 'blanks', 'name' => 'ЛДСП и заготовки'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                    ],
                    'models' => [
                        ['key' => 'wardrobe', 'name' => 'Шкаф'],
                        ['key' => 'kitchen', 'name' => 'Кухня'],
                        ['key' => 'bed', 'name' => 'Кровать'],
                        ['key' => 'table', 'name' => 'Стол'],
                    ],
                ],
            ],
            [
                'preset_key' => 'windows',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'windows',
                            'name' => 'Окна',
                            'services' => [
                                ['name' => 'Регулировка окна', 'price' => 1200],
                                ['name' => 'Замена уплотнителя', 'price' => 1000],
                                ['name' => 'Замена стеклопакета', 'price' => 3500],
                                ['name' => 'Замена ручки', 'price' => 700],
                            ],
                        ],
                        [
                            'key' => 'doors',
                            'name' => 'Двери',
                            'services' => [
                                ['name' => 'Установка двери', 'price' => 4500],
                                ['name' => 'Регулировка двери', 'price' => 900],
                                ['name' => 'Замена замка', 'price' => 2000],
                                ['name' => 'Установка доводчика', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'nets',
                            'name' => 'Москитные сетки',
                            'services' => [
                                ['name' => 'Изготовление сетки', 'price' => 1800],
                                ['name' => 'Установка сетки', 'price' => 500],
                            ],
                        ],
                        [
                            'key' => 'balconies',
                            'name' => 'Балконы',
                            'services' => [
                                ['name' => 'Остекление балкона', 'price' => 25000],
                                ['name' => 'Отделка балкона', 'price' => 15000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'fittings', 'name' => 'Фурнитура'],
                        ['key' => 'seals', 'name' => 'Уплотнители'],
                        ['key' => 'nets-goods', 'name' => 'Сетки'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                    ],
                    'models' => [
                        ['key' => 'pvc-window', 'name' => 'Окно ПВХ'],
                        ['key' => 'pvc-door', 'name' => 'Дверь ПВХ'],
                        ['key' => 'balcony', 'name' => 'Балконный блок'],
                    ],
                ],
            ],
            [
                'preset_key' => 'cleaning',
                'version' => 1,
                'content' => [
                    'categories' => [
                        [
                            'key' => 'premises',
                            'name' => 'Уборка помещений',
                            'services' => [
                                ['name' => 'Поддерживающая уборка', 'price' => 3000],
                                ['name' => 'Генеральная уборка', 'price' => 6000],
                                ['name' => 'Уборка после ремонта', 'price' => 9000],
                            ],
                        ],
                        [
                            'key' => 'windows',
                            'name' => 'Окна',
                            'services' => [
                                ['name' => 'Мойка окна', 'price' => 800],
                                ['name' => 'Мойка витрины', 'price' => 1500],
                            ],
                        ],
                        [
                            'key' => 'furniture',
                            'name' => 'Мебель и текстиль',
                            'services' => [
                                ['name' => 'Химчистка дивана', 'price' => 3500],
                                ['name' => 'Химчистка ковра', 'price' => 2500],
                                ['name' => 'Химчистка матраса', 'price' => 2000],
                            ],
                        ],
                        [
                            'key' => 'special',
                            'name' => 'Спецработы',
                            'services' => [
                                ['name' => 'Дезинфекция', 'price' => 4000],
                                ['name' => 'Уборка снега', 'price' => 2000],
                            ],
                        ],
                    ],
                    'productCategories' => [
                        ['key' => 'chemistry', 'name' => 'Химия и средства'],
                        ['key' => 'inventory', 'name' => 'Инвентарь'],
                        ['key' => 'consumables', 'name' => 'Расходники'],
                    ],
                    'models' => [
                        ['key' => 'flat', 'name' => 'Квартира'],
                        ['key' => 'house', 'name' => 'Дом'],
                        ['key' => 'office', 'name' => 'Офис'],
                        ['key' => 'shop', 'name' => 'Магазин'],
                    ],
                ],
            ],
        ];
    }
}
