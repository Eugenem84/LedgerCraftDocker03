# API: контракт синхронизации и эндпоинтов (канонический)

> **Источник истины** для контракта API. Клиент (`ledger-craft-offline-first-PS`)
> держит у себя только потребительскую выжимку — `docs/API-INTEGRATION.md`.
> Составлено по коду: `routes/api.php`, `app/Http/Controllers/SyncController.php`,
> `app/Http/Controllers/ProductController.php`, `app/Repositories/`.

## Общие сведения

| Параметр | Значение |
|---|---|
| Base URL (dev) | `https://dev.medovf2h.beget.tech/api` |
| Идентификация устройства | заголовок `X-Sync-ID` (UUID из localStorage клиента) |
| Формат | JSON |
| Авторизация | Laravel Sanctum (`/api/register`, `/api/login`, `/api/me`, `/api/logout`); **синк требует токен** |
| БД | PostgreSQL (docker); `.env.example` по умолчанию `mysql` — привести к `pgsql` |

`X-Sync-ID` — это **не авторизация**, а метка устройства для анти-эха синхронизации.
С задачи 3.10 `/api/sync` и `/api/sync-updates` — под `auth:sanctum`: владелец данных
(`user_id` напрямую или через цепочку родителей) определяет, что устройство видит и меняет.

## 1. `POST /api/sync` — приём локальных изменений

Реализация: `SyncController::sync()`. Весь пакет оборачивается в `DB::transaction`.

```json
{
  "operations": [
    {
      "type": "insert",              // insert | update | delete
      "table": "orders",
      "payload": {
        "local_id": "<UUID>",        // клиентский идентификатор операции
        "field": "value"
      }
    }
  ]
}
```

Ответ:

```json
{
  "synced": [ { "type": "insert", "local_id": "<UUID>", "server_id": 12345, "updated_at": "2026-09-12T10:00:00.000000Z" } ],
  "errors": [ { "local_id": "<UUID>", "error": "DATABASE_ERROR", "details": { "message": "..." } } ]
}
```

Детали:
- **ответ содержит `server_id`**, не `id` — клиент пишет его в локальный `server_id`;
- ✅ **ответ приходит по каждой операции** (задача 3.5): `update`/`delete` подтверждаются даже
  при `affected = 0`; `update` несуществующей записи → `RECORD_NOT_FOUND`. Клиент считает
  операцию доставленной **только** по явному ответу, иначе возвращает её в очередь;
- ✅ **`updated_at` в ответе** (задача 3.8): у каждой подтверждённой операции есть версия
  записи в **ISO-8601 UTC** (`2026-09-12T10:00:00.000000Z`) — это ровно то значение, которое
  записано в БД (секундная точность, `Carbon::now()->startOfSecond()`, колонки `timestamp(0)`).
  Клиент сохраняет её локально, чтобы его «своя» версия не разошлась с сервером и более
  старая копия не воскрешала запись (**last-write-wins**). У `update` это новая версия, у
  `delete` — момент операции (у `order_service` и hard-delete своего `server_id` нет);
- `localId = payload.uuid_id ?? payload.local_id ?? op.id`;
- из payload **вырезаются** `id`, `local_id`, `uuid_id`, а также `server_id` и `*_server_id`;
- ✅ **идемпотентность** (задача 3.5): `insert` — «найти или вставить/обновить» по
  `uuid_id = localId` (уникальный индекс у всех синкаемых таблиц); у `order_service` ключ —
  `order_id + service_id`. Повторная отправка того же батча дублей не создаёт, `created_at`
  существующей записи не перезаписывается;
- ✅ **частичный откат** (перенос из Go, задача 3.11): каждая операция обёрнута в
  `SAVEPOINT sync_op`; ошибка → `ROLLBACK TO SAVEPOINT`, остальной батч применяется
  (на PostgreSQL без этого одна ошибка «вешала» транзакцию);
- `errors`:
  - `Invalid operation structure or table.` — неизвестная таблица / битая структура;
  - `MISSING_ID_FOR_UPDATE` / `MISSING_ID_FOR_DELETE` — нет `id` (серверного);
  - `RECORD_NOT_FOUND` — `update` по несуществующему `id` **или** попытка изменить/удалить
    запись чужого пользователя (задача 3.10 — существование не подтверждаем);
  - `FORBIDDEN_NOT_OWNER` — вставка привязана к чужому родителю (задача 3.10);
  - `DATABASE_ERROR` / `GENERAL_ERROR` — с `details`;
- ✅ `last_sync_id` (анти-эхо, задача 3.6): колонка есть у всех синкаемых таблиц, сервер
  проставляет её значением `X-Sync-ID` при insert/update/soft-delete;
- ✅ **владелец данных** (задача 3.10): маршрут под `auth:sanctum`; `insert` проставляет `user_id`
  из токена там, где колонка есть (`orders`, `specializations`), и проверяет владельца родителей
  из payload; `update`/`delete` работают только со своими записями; «ничьи» строки
  (`user_id`/родитель = NULL — данные до 3.10) считаются общими и видны всем — их нужно разово
  привязать к пользователю;
- ✅ **удаления** (задача 3.9): таблицы с `deleted_at` (`clients`, `products`, `services`,
  `categories`, `equipment_models`, `orders`, `order_service`) удаляются soft-delete'ом; у
  остальных строка удаляется физически, а факт удаления пишется в `sync_tombstones`
  (`table_name + record_id` — повторный `DELETE` tombstone не дублирует, задача 3.5);
- ✅ **деньги — целые рубли** (задача 3.12): `services.price` был VARCHAR → integer; payload
  нормализуется (`'1 500,50'` → `1501`, `''` у услуги → `0`, нечисловое → `null`), в расчётах
  больше нет `CAST(... AS numeric)`.

### Спец-обработка таблиц

- **`orders`**: принимаются только `specialization_id, client_id, hours, minutes, total_amount, comments`
  (остальные поля при insert теряются). `total_amount` — **в рублях**, без конверсии.
- **`order_service`**: ждёт `order_id`/`service_id` уже как **серверные** ID, `sale_price`,
  `quantity`; если `sale_price` пуст — берётся `price` из `services`.
  ✅ `insert` дедуплицируется по `order_id + service_id` (у связки нет своего PK), `delete` —
  тоже по натуральному ключу (`payload.order_id` + `payload.service_id`); `uuid_id` хранит
  клиентский id строки — по нему клиент сопоставляет запись с серверной (задача 3.5).

### `$tables` — что принимает синк

```
clients, specializations, orders, equipment_models, incoming_products, materials,
order_product, order_service, products, product_categories, product_stocks, categories,
services, buy_product_prices, sales_products_prices
```

## 2. `GET /api/sync-updates` — инкрементальная выдача

Реализация: `SyncController::fetchUpdates()`.

```
GET /api/sync-updates?table=<table>&since=<ms>
Headers: X-Sync-ID: <uuid>
```

- `since` — миллисекунды (`Carbon::createFromTimestampMs`);
- таблица не из `$tables` → `400 { "error": "Invalid or missing table" }`;
- ✅ владелец данных (задача 3.10): выдаются только записи пользователя из токена (цепочка
  `specializations.user_id` / `orders.user_id`); «ничьи» (legacy) строки тоже отдаются;
- ✅ анти-эхо (`last_sync_id`, задача 3.6): записи с `last_sync_id == X-Sync-ID` исключаются —
  устройство не получает свои же изменения; правка чужого устройства вернёт запись автору;
- ✅ **удаления (задача 3.9):**
  - у таблиц с `deleted_at` soft-deleted строки **больше не отфильтровываются** — они приходят
    с `deleted: true` и `deleted_at`, по ним клиент удаляет запись у себя;
  - у таблиц без `deleted_at` добавляются tombstones из `sync_tombstones` в виде
    `{ id: <server_id>, uuid_id, deleted: true, deleted_at, updated_at }`;
  - soft-delete двигает `updated_at`, поэтому удаление видно выдаче по курсору;
- сортировка `ORDER BY updated_at`;
- ✅ единый стандарт времени (задача 3.8): `created_at`/`updated_at`/`deleted_at` отдаются
  строками **ISO-8601 UTC** (`2026-09-12T10:00:00.000000Z`). «Сырое» `2026-09-12 10:00:00`
  клиентский `Date.parse` трактует как ЛОКАЛЬНОЕ время устройства — версии (last-write-wins)
  и курсор выдачи смещались бы на часовой пояс.

Ответ: `{ "table": "clients", "count": 2, "records": [ { "id": 1, "deleted": false, ... } ] }`
(`id` — **серверный**, `deleted` — признак удаления). Для вставки на клиенте нужны
`created_at`/`updated_at` (ISO-8601 UTC).

## 3. `POST /api/arrival_product` — приход товара

Реализация: `ProductController::arrival()`.

```json
{ "product_id": 12, "base_sale_price": 500, "by_price": 300, "arrival_quantity": 10, "supplier": "ООО" }
```

- валидация: `product_id` обязателен и должен существовать в `product_stocks.product_id`;
- три эффекта: обновление `products.base_sale_price`, увеличение `product_stocks.quantity`,
  запись в `incoming_products`;
- явного `return` нет → пустой ответ 200.

## 4. Авторизация

| Метод | Путь |
|---|---|
| POST | `/api/register` |
| POST | `/api/login` |
| POST | `/api/logout` (sanctum) |
| GET | `/api/me` (sanctum) |
| DELETE | `/api/delete-account` (sanctum) |
| POST | `/api/forgot-password`, `/api/reset-password` |
| POST | `/api/sync` (sanctum) — задача 3.10 |
| GET | `/api/sync-updates` (sanctum) — задача 3.10 |
| GET | `/api/get_orders_by_user` (sanctum) — только свои заказы |

Без токена синк отвечает `401`. `GET /api/get_orders_by_user/{id}` удалён: он отдавал заказы
любого пользователя (IDOR), а публичный `/api/get_orders_by_user` падал в 500 на `Auth::user()`.

`PUT /api/update_paid_status/{id}` с телом `{paid}` — единственная ручка смены статуса
оплаты; дубль `switch_paid_status` удалён в задаче 7.6 (web-компонент `HistoryOrders.vue`
переведён на неё). Группа `auth:api` (token-guard без `api_token` у `users`) тоже удалена
как недостижимая — для API используется `auth:sanctum`.

## 5. Прочие эндпоинты

| Метод | Путь | Контроллер |
|---|---|---|
| GET | `/api/get_product_stocks/{productCategoryId}` | `ProductStockController` |
| GET | `/api/get_products/{productCategoryId}` | `ProductController` |
| POST | `/api/add_product`, `/api/edit_product`, `/api/delete_store_product` | `ProductController` |
| GET | `/api/get_product_categories/{specializationId}` | `ProductCategoryController` |
| GET | `/api/get_categories/{specializationId}` | `CategoryController` |
| GET | `/api/get_service/{categoryId}` | `ServiceController` |
| GET | `/api/get_materials_by_order/{orderId}` | `MaterialController` |
| GET | `/api/orders_by_specialization/{id}` | `OrderController` |
| GET | `/api/order/{orderId}`, `/api/get_services/{orderId}` | `OrderController` |
| POST | `/api/order-report/{order}/share-link` | `OrderController::generateShareLink` |
| GET | `/api/get_total_DWYM/{id}`, `/api/get_top_services/{id}`, `/api/income_by_year/{id}` | `StatisticController` |
| GET | `/api/app-quasar-android-version`, `/api/download-apk`, `/api/hcp/chcp.json` | `AppVersionController` |

## 6. Ожидания по FK (клиент конвертирует локальные id → server_id)

| Таблица | FK |
|---|---|
| clients | specialization_id → specializations |
| categories | specialization_id → specializations |
| services | category_id → categories |
| product_categories | specialization_id → specializations |
| products | product_category_id → product_categories |
| orders | client_id, specialization_id, model_id |
| order_service | order_id → orders; service_id → services |
| equipment_models | specialization_id → specializations |

## 7. Известные расхождения/риски (серверные задачи)

> Приоритеты: **P0** — ломаются данные/синк, **P1** — больно поддерживать, **P2** — хочется.
> Номера вида «задача 3.9» — задачи клиентского трекера, где те же работы вплетены с метками
> `[BE]` / `[FE+BE]`.

- [x] **P0 · Удаления не доезжают (задача 3.9). Сделано.** `tableHasSoftDeletes()` больше не список,
  а проверка схемы (`Schema::hasColumn($table, 'deleted_at')`) — так в soft-delete попали `orders`,
  `equipment_models` и `order_service`. `fetchUpdates` перестал отфильтровывать soft-deleted строки
  (они и есть tombstone, приходят с `deleted: true`), а для таблиц без `deleted_at` заведены
  tombstones: миграция `2026_09_14_000000_create_sync_tombstones_table`, запись при физическом
  удалении, выдача в `sync-updates`. Повторный `DELETE` tombstone не дублирует. Тесты:
  `::test_order_deletion_reaches_another_device`, `::test_hard_delete_is_returned_as_tombstone`,
  `::test_order_service_deletion_reaches_another_device_and_can_be_revived`.
- [x] **P0 · Владелец данных (задача 3.10). Сделано.** `/api/sync` и `/api/sync-updates` — под
  `auth:sanctum`; `insert` проставляет `user_id` из токена (`orders`, `specializations`) и проверяет
  владельца родителей, `update`/`delete` работают только со своими записями, выдача фильтруется по
  цепочке владельцев (`specializations.user_id`/`orders.user_id`). IDOR закрыт: удалён
  `GET /get_orders_by_user/{id}` и публичный дубль. Тесты: `::test_sync_requires_authentication`,
  `::test_device_sees_only_its_owners_data`, `::test_cannot_update_or_delete_foreign_record`,
  `::test_cannot_insert_child_into_foreign_parent`, `::test_order_insert_sets_owner`.
  ⚠️ «Ничьи» строки (`user_id`/родитель = NULL, данные до 3.10) видны всем — их нужно разово
  привязать к пользователю; клиентский вход/токен — задача 7.4.
- [x] **P1 · Типы денег (задача 3.12). Сделано.** Миграция
  `2026_09_15_000000_services_price_to_integer`: `services.price` VARCHAR → integer (нечисловое → 0,
  десятичные округляются). Остальные денежные колонки уже integer/bigint — менять нечего.
  `CAST(... AS numeric)` из `StatisticRepository` убраны, payload синка нормализуется
  (`normalizeMoney`/`toRubles`). Тесты: `::test_service_price_is_integer_and_payload_is_normalized`,
  `::test_order_service_sale_price_is_numeric_and_statistics_work`.
- [x] **P0 · Идемпотентность (задача 3.5). Сделано.** Миграция
  `2026_09_12_000000_add_uuid_id_to_sync_tables` добавляет `uuid_id` (nullable, unique) всем
  синкаемым таблицам — это клиентский `local_id`. `INSERT` идёт через «найти или
  вставить/обновить» по `uuid_id`, у `order_service` (нет PK) — по `order_id + service_id`,
  там же и `delete`. Каждая операция всегда получает явный ответ. Тест:
  `tests/Feature/SyncControllerTest.php` (повторный батч не даёт дублей, битая операция не
  срывает батч, `server_id`/`*_server_id` вырезаются из payload).
- [x] **P1 · SAVEPOINT-изоляция операций (часть 3.11). Сделано** вместе с 3.5: `SAVEPOINT sync_op`
  на операцию + `ROLLBACK TO SAVEPOINT` при ошибке — одна битая операция не «вешает» транзакцию
  на PostgreSQL и не откатывает остальной батч. Модуль Go вынесен из проекта (см. следующий пункт).
- [x] **P1 · Go-сайдкар `sync/` (решение D1). Сделано (задача 3.11).** `/api/sync` и
  `/api/sync-updates` — единственный транспорт синхронизации. Go-код (`sync/`, `_docker/sync/`)
  и сервис `sync` из `docker-compose.yaml` удалены из проекта; копия сохранена в песочнице
  `../ledger-craft-go-sync-sandbox` (с README, почему и как вернуть). Нужное из Go-реализации
  уже в Laravel: `SAVEPOINT`-изоляция и вырезание `server_id`/`*_server_id` (задача 3.5).
- [ ] **P1 · `orders` в синке.** При `insert` принимаются только `specialization_id, client_id,
  hours, minutes, total_amount, comments` — теряются `status`, `paid`, `model_id`, `share_token`
  и `user_order_number` (владелец `user_id` проставляется с задачи 3.10). Нужно: расширить список колонок.
- [x] **P1 · `last_sync_id` (задача 3.6). Сделано.** Миграция
  `2026_09_13_000000_add_last_sync_id_to_sync_tables` добавила `last_sync_id` (nullable, index)
  всем синкаемым таблицам. `SyncController` проставляет её при insert (включая `order_service`),
  update и soft-delete, а `fetchUpdates` отдаёт устройству только чужие записи:
  `last_sync_id != X-Sync-ID OR last_sync_id IS NULL`. Тесты:
  `SyncControllerTest::test_own_changes_are_not_echoed_back_to_the_device`,
  `::test_change_by_another_device_comes_back_to_the_author`,
  `::test_order_service_insert_is_not_echoed_to_the_same_device`.
- [ ] **P1 · Методика «выручки» (задача 9.1).** В `StatisticRepository` три разных формулы:
  `SUM(services.price)`, `SUM(quantity * sale_price)`, `SUM(orders.total_amount)`
  (последнее — без фильтров `paid`/`status`) → цифры на одном экране не сойдутся.
- [ ] **P1 · Материалы (решение D2, задачи 3.4/9.6).** Серверная `materials` — это **строки
  материалов заказа** (`order_id, name, price, amount`); таблицы `order_material` на сервере нет и
  не создаётся. На клиенте решено: ручные позиции синкаются под одним именем (предлагается
  `order_material`), клиентский «справочник материалов» (миграция 018) удаляется, в позиции
  добавляется `buy_price`.
- [ ] **P1 · Маржа (задача 9.5).** `buy_product_prices.buy_price` и `incoming_products.by_price`
  не читаются ни в одном расчёте: «прибыль» в отчётах равна выручке. Нужно: `buy_price` в позициях
  заказа (со склада — из закупки, вручную — из формы) + расчёт маржи.
- [ ] **P2 · `arrival_product` (задача 9.2).** Нет явного `return`, три записи без транзакции, нет
  идемпотентности (повторный приход удваивает остаток).
- [x] **P2 · Гигиена роутов (задача 7.6). Сделано.** Удалены: недостижимая группа `auth:api`
  (`/get_all_specializations` без `api_token` у `users` — web-часть по-прежнему использует свой
  маршрут в `routes/web.php`), дубль `PUT /switch_paid_status/{id}` (вместе с методами
  `OrderController::switchPaidStatus` и `OrderRepository::switchPaidStatus`; осталась одна ручка
  `update_paid_status` с `{paid}`, web-компонент переведён на неё), мёртвый
  `MaterialController::create` (не зароутен, аргументы перепутаны к тому же тянул
  `SebastianBergmann\CodeCoverage\Driver\Selector`). `get_orders_by_user` оставлен один на группу
  (API — `/api/get_orders_by_user` под `auth:sanctum`, web — свой маршрут): у них разные middleware,
  это не дубль. Scaffold `app/Http/Controllers/Auth/*` и `Auth::routes()` **не трогаем** — web-версия
  признана продуктом (решение по открытому вопросу).
- [ ] **P2 · Тесты `/sync` (задача 5.6).** PHPUnit: SAVEPOINT-изоляция, идемпотентность, порядок
  «родитель → ребёнок», удаления/tombstones, вырезание `*_server_id`.
- [ ] **P2 · Лимит выдачи.** `fetchUpdates` без `limit`/пагинации — устройство после долгого
  офлайна получает таблицу целиком.
- [x] **P2 · Открытый вопрос: web-версия. Решён (12.09.2026).** Web-часть (`resources/js`,
  blade'ы + `Auth::routes()` + `/order-report`) признана **продуктом**, а не легаси: её не удаляем,
  поэтому объём 7.6 ограничен чисткой мёртвого/дублирующего (см. выше). Публичный отчёт
  `/order-report` связан с share-ссылкой (задача 9.4).
- [x] `$tables` приведён к реальным таблицам (`buy_product_prices`, `sales_products_prices`; убрана
  `service_categories`).
- [x] Удалён `#region agent log` из `SyncController.php`.
