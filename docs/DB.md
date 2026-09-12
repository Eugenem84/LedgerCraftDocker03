# DB: серверная схема (PostgreSQL)

> Полные колонки и история изменений — в `database/migrations/` (53 миграции).
> Здесь — карта таблиц, назначение и важные нюансы. Локальная схема клиента описана
> в репозитории фронта (`docs/DATA-MODEL.md`) и отличается.

## Синхронизируемые таблицы

| Таблица | Назначение | Ключевые колонки / нюансы |
|---|---|---|
| `specializations` | специализации мастерской | название — в колонке **`specializationName`** (camelCase; `/sync-updates` отдаёт её как есть, клиент переименовывает в `name`); `user_id` (FK, владелец); профиль Фазы 10: `preset_key`, `accent`, `features` (text/JSON), `archived`, `template_version`; легаси `popularCounter` (NOT NULL — синк проставляет `0` при insert) |
| `clients` | клиенты | `name`, `phone`, `specialization_id`, `deleted_at` (soft) |
| `categories` | категории работ | `category_name`, `specialization_id`, `template_key` (nullable — пометка пресета, Фаза 10) |
| `services` | работы (услуги) | `service`, `price` (**integer** после 3.12), `category_id`, `deleted_at` |
| `product_categories` | категории товаров | `specialization_id`, `template_key` (nullable — пресет), связь с товарами |
| `products` | товары (склад) | `name`, `manufacturer`, `product_number`, `weight` (decimal), `base_sale_price` (int), `deleted_at` |
| `product_stocks` | остатки на складе | `product_id` (FK, NOT NULL), `quantity`, `supplier` (nullable). **Одна строка на товар** (`Product::stock()` — hasOne); «где лежит товар» знает `products.product_category_id` (прежний дубль `product_categories_id` удалён миграцией `2026_09_16_000000`, задача 9.3). Синк/владелец адресуют строку товаром |
| `buy_product_prices` | закупочные цены (история) | `product_id` (FK), `buy_price` (int); пишет приход (9.2), читается складом и маржой (9.3/9.5) |
| `sales_products_prices` | цены продажи по заказам | `product_id`, `order_id`, `sale_price` (int), `uuid_id` — пишет приложение в момент продажи товара (9.3); склад читает последнюю цену продажи |
| `incoming_products` | приходы товара | `product_id` (FK), `supplier` (**NOT NULL**), `quantity`, **`by_price`** (int), `uuid_id` — ключ идемпотентности прихода: повтор не удваивает `product_stocks.quantity` (9.2) |
| `orders` | заказы | `specialization_id`, `client_id`, `hours`, `minutes`, `total_amount` (**рубли**, int), `comments`, `status`, `paid`, `model_id`, `user_order_number`, `share_token`, `equipment_identifier` (nullable — VIN/серийник/адрес объекта, Фаза 10); колонка `materials` удалена |
| `order_service` | связка заказ↔работа | `order_id`, `service_id`, `sale_price`, `quantity`, `uuid_id` (миграция 2026), timestamps; **без PK** (композитный ключ закомментирован) |
| `order_product` | связка заказ↔товар | `order_id`, `product_id`, `sale_price`, `quantity`, `buy_price` (int, nullable — себестоимость на момент продажи, миграция `2026_09_17_000000`, задачи 9.5/9.6) |
| `materials` | **строки материалов заказа** (ручные позиции: «мастер купил на стороне») | `order_id` (FK, NOT NULL), `name`, `price` (bigint), `amount` (smallint), `buy_price` (int, nullable — себестоимость ручной позиции, миграция `2026_09_17_000000`); колонок `specialization_id`/`deleted_at` **нет** (удаление — через `sync_tombstones`). Клиентский «справочник материалов» аналога на сервере не имеет — обе стороны сведены к одной таблице (D2, задача 9.6) |
| `equipment_models` | модели техники | `name`, `specialization_id`, `template_key` (nullable — пресет), `deleted_at` |

## Служебные таблицы

`users`, `personal_access_tokens` (Sanctum), `password_reset_tokens`, `password_resets`,
`failed_jobs`, `migrations`.

## Контент (не синкается)

- `specialization_templates` (миграция `2026_09_18_030000`, Фаза 10, задача 10.7;
  наполняется сидом — задача **11.3**) — пресеты специализаций: `preset_key` (unique),
  `version`, `content` (JSON: категории → услуги с ценами, категории товаров, модели).
  Контент лежит в `database/seeders/SpecializationTemplateSeeder.php` (формат клиентских
  пресетов `src/domain/presets/*` на клиенте, но **только каталог** — лексикон, акцент и
  флаги вкладок остаются на клиенте, решение D5). Сид идемпотентен (`updateOrCreate` по
  `preset_key`) и тянется из `DatabaseSeeder`:
  `php artisan db:seed --class=SpecializationTemplateSeeder --force`. Отдаётся
  `GET /api/specialization-templates` под `auth:sanctum`; клиент держит read-only кэш в `meta`
  и офлайн работает из него, с фолбэком на клиентские JSON (`src/domain/presets/*`).
  Это контент, а не данные пользователя, поэтому через синк не ходит. Тест —
  `tests/Feature/SpecializationTemplateSeederTest.php`.

## Нюансы

- **Деньги** — целые числа в **рублях** (совпадает с клиентом после задачи 2.3).
- ✅ **Удаления (задача 3.9):** `SyncController::tableHasSoftDeletes()` спрашивает схему
  (`Schema::hasColumn($table, 'deleted_at')`), поэтому soft-delete работает для всех таблиц
  с `deleted_at` (`clients`, `services`, `categories`, `equipment_models`, `products`, `orders`,
  `order_service`). У таблиц без `deleted_at` факт физического удаления пишется в `sync_tombstones`
  (`2026_09_14_000000`), а `sync-updates` отдаёт удаления как `deleted: true` — клиент убирает
  запись у себя.
- ✅ **Владелец (задача 3.10):** `/sync` и `/sync-updates` — под `auth:sanctum`; `user_id`
  проставляется при вставке, `update`/`delete` работают только со своими записями, выдача
  фильтруется по цепочке владельцев (`specializations.user_id` → `orders.user_id` → дети).
- ✅ **Go-сайдкар вынесен из проекта (решение D1, задача 3.11):** синк — единственный транспорт
  Laravel; Go-код (`sync/`, `_docker/sync/`) и сервис `sync` в `docker-compose.yaml` удалены
  (копия — в песочнице `../ledger-craft-go-sync-sandbox`). Из него в Laravel перенесены
  `SAVEPOINT`-изоляция и вырезание `server_id`/`*_server_id`.
- **`uuid_id`** — ✅ теперь у **всех** синкаемых таблиц (миграция
  `2026_09_12_000000_add_uuid_id_to_sync_tables`, nullable + unique): это клиентский `local_id`,
  ключ идемпотентности синка (задача 3.5). У `order_service` колонка появилась ещё в
  `2026_04_05_110000`; у неё же нет своего PK, поэтому там ключ — `order_id + service_id`.
- **Идемпотентность синка:** `INSERT` — «найти или вставить/обновить» по `uuid_id`; повторная
  отправка батча дублей не создаёт, `created_at` существующей записи не перезаписывается.
  `update`/`delete` всегда подтверждаются (`affected = 0` — тоже), `update` несуществующей
  записи → `RECORD_NOT_FOUND`. Каждая операция изолирована `SAVEPOINT`'ом.
- **`last_sync_id`** — ✅ колонка есть у **всех** синкаемых таблиц (миграция
  `2026_09_13_000000_add_last_sync_id_to_sync_tables`, nullable + index) и заполняется значением
  `X-Sync-ID` при insert/update/soft-delete. Анти-эхо (задача 3.6): `fetchUpdates` отдаёт
  устройству только записи с чужим `last_sync_id` (или пустым) — своё изменение оно уже
  применило локально и обратно не получает.
- **`order_service`** не имеет собственного PK и timestamps изначально; `updated_at`/`uuid_id`
  добавлены отдельными миграциями.
- ✅ `services.price` — `integer` (миграция `2026_09_15_000000_services_price_to_integer`,
  задача 3.12); `CAST(... AS numeric)` из расчётов убран.

## Go-сайдкар — вынесен из проекта

Экспериментальный Go-сервис синхронизации (`ledgercraft/sync`: `POST /sync` напрямую с Postgres
через `pgx`) вынесен из репозитория в песочницу `../ledger-craft-go-sync-sandbox` (задача 3.11,
решение D1): контейнер `sync` и порт `:8081` из проекта удалены. Единственный транспорт синка —
Laravel (`docs/API.md`); актуальному контракту Go-реализация не соответствовала (устаревший список
таблиц), а нужное из неё — `SAVEPOINT`-изоляция операций и вырезание `server_id`/`*_server_id` —
уже в `SyncController`.
