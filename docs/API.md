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
| Авторизация | Laravel Sanctum (`/api/register`, `/api/login`, `/api/me`, `/api/logout`) |
| БД | PostgreSQL (docker); `.env.example` по умолчанию `mysql` — привести к `pgsql` |

`X-Sync-ID` — это **не авторизация**, а метка устройства для анти-эха синхронизации.

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
  "synced": [ { "type": "insert", "local_id": "<UUID>", "server_id": 12345 } ],
  "errors": [ { "local_id": "<UUID>", "error": "DATABASE_ERROR", "details": { "message": "..." } } ]
}
```

Детали:
- **ответ содержит `server_id`**, не `id` — клиент пишет его в локальный `server_id`;
- ✅ **ответ приходит по каждой операции** (задача 3.5): `update`/`delete` подтверждаются даже
  при `affected = 0`; `update` несуществующей записи → `RECORD_NOT_FOUND`. Клиент считает
  операцию доставленной **только** по явному ответу, иначе возвращает её в очередь;
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
  - `RECORD_NOT_FOUND` — `update` по несуществующему `id`;
  - `DATABASE_ERROR` / `GENERAL_ERROR` — с `details`;
- ✅ `last_sync_id` (анти-эхо, задача 3.6): колонка есть у всех синкаемых таблиц, сервер
  проставляет её значением `X-Sync-ID` при insert/update/soft-delete.

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
- ✅ анти-эхо (`last_sync_id`, задача 3.6): записи с `last_sync_id == X-Sync-ID` исключаются —
  устройство не получает свои же изменения; правка чужого устройства вернёт запись автору;
- soft-delete (`deleted_at IS NULL`) — только для `clients, products, services, categories`;
- сортировка `ORDER BY updated_at`.

Ответ: `{ "table": "clients", "count": 2, "records": [ { "id": 1, ... } ] }`
(`id` — **серверный**). Для вставки на клиенте нужны `created_at`/`updated_at`.

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

- [ ] **P0 · Удаления не доезжают (задача 3.9).** `tableHasSoftDeletes()` знает только
  `clients, products, services, categories`, а `deleted_at` есть ещё у `orders`
  (`2026_02_11_133000`), `equipment_models` и `order_service` (`2026_03_04_162502`). Удаление заказа
  через `/sync` — жёсткое, а `sync-updates` отдаёт уже удалённые заказы обратно — на клиенте фантом.
  Нужно: расширить список + отдавать tombstones (флаг/`include_deleted`).
- [ ] **P0 · Владелец данных (задача 3.10).** `/sync` и `/sync-updates` без auth; `X-Sync-ID` — не
  авторизация; `orders.user_id`/`user_order_number` при insert из синка теряются; выдача не
  фильтруется по пользователю. Плюс IDOR: `GET /get_orders_by_user/{id}` отдаёт заказы любого
  пользователя. Нужно: Sanctum, сохранение владельца, фильтр выдачи.
- [x] **P0 · Идемпотентность (задача 3.5). Сделано.** Миграция
  `2026_09_12_000000_add_uuid_id_to_sync_tables` добавляет `uuid_id` (nullable, unique) всем
  синкаемым таблицам — это клиентский `local_id`. `INSERT` идёт через «найти или
  вставить/обновить» по `uuid_id`, у `order_service` (нет PK) — по `order_id + service_id`,
  там же и `delete`. Каждая операция всегда получает явный ответ. Тест:
  `tests/Feature/SyncControllerTest.php` (повторный батч не даёт дублей, битая операция не
  срывает батч, `server_id`/`*_server_id` вырезаются из payload).
- [x] **P1 · SAVEPOINT-изоляция операций (часть 3.11). Сделано** вместе с 3.5: `SAVEPOINT sync_op`
  на операцию + `ROLLBACK TO SAVEPOINT` при ошибке — одна битая операция не «вешает» транзакцию
  на PostgreSQL и не откатывает остальной батч. Осталось по 3.11: вынести Go-сайдкар из `master`
  и убрать сервис `sync` из `docker-compose.yaml`.
- [ ] **P1 · Go-сайдкар `sync/` (решение D1).** Дублирует контракт с устаревшим `allowedTables`
  (`service_categories`, `by_product_prices`, `sales_product_prices`) и пустым `tablesWithLastSyncID`;
  к nginx/Traefik не подключён. Решение: единственный транспорт — Laravel, Go выносится из `master`
  в песочницу. ✅ Перенос `SAVEPOINT`-изоляции и вырезания `server_id`/`*_server_id` в Laravel уже
  сделан (задача 3.5); осталось убрать сервис `sync` из `docker-compose.yaml` и вынести код.
- [ ] **P1 · `orders` в синке.** При `insert` принимаются только `specialization_id, client_id,
  hours, minutes, total_amount, comments` — теряются `status`, `paid`, `model_id`, `share_token`,
  а также `user_id`/`user_order_number`. Нужно: расширить список колонок.
- [x] **P1 · `last_sync_id` (задача 3.6). Сделано.** Миграция
  `2026_09_13_000000_add_last_sync_id_to_sync_tables` добавила `last_sync_id` (nullable, index)
  всем синкаемым таблицам. `SyncController` проставляет её при insert (включая `order_service`),
  update и soft-delete, а `fetchUpdates` отдаёт устройству только чужие записи:
  `last_sync_id != X-Sync-ID OR last_sync_id IS NULL`. Тесты:
  `SyncControllerTest::test_own_changes_are_not_echoed_back_to_the_device`,
  `::test_change_by_another_device_comes_back_to_the_author`,
  `::test_order_service_insert_is_not_echoed_to_the_same_device`.
- [ ] **P1 · Типы денег (задача 3.12).** `services.price` — VARCHAR (в SQL приходится писать
  `CAST(... AS numeric)`), `materials.price` — `decimal(10,2)`, суммы заказов — целые рубли.
  Привести к целым рублям.
- [ ] **P1 · Методика «выручки» (задача 9.1).** В `StatisticRepository` три разных формулы:
  `SUM(CAST(services.price AS numeric))`, `SUM(quantity * sale_price)`, `SUM(orders.total_amount)`
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
- [ ] **P2 · Гигиена роутов (задача 7.6).** `GET /get_orders_by_user` объявлен трижды (первая,
  публичная версия падает в 500 на `Auth::user()`, рабочая sanctum-версия недостижима);
  `update_paid_status` + `switch_paid_status` дублируют операцию; `auth:api` (token-guard без
  `api_token` у `users`) — тупик; не зароутенный `MaterialController::create` (аргументы перепутаны);
  scaffold `app/Http/Controllers/Auth/*` при своём `AuthController`.
- [ ] **P2 · Тесты `/sync` (задача 5.6).** PHPUnit: SAVEPOINT-изоляция, идемпотентность, порядок
  «родитель → ребёнок», удаления/tombstones, вырезание `*_server_id`.
- [ ] **P2 · Лимит выдачи.** `fetchUpdates` без `limit`/пагинации — устройство после долгого
  офлайна получает таблицу целиком.
- [ ] **P2 · Открытый вопрос: web-версия.** `resources/js` (Vue 3 + Vite + Bootstrap/Vuetify/jQuery),
  blade'ы + `Auth::routes()` + `order-report` — второй клиент или легаси? От ответа зависит объём
  задачи 7.6; публичный отчёт `/order-report` связан с share-ссылкой (задача 9.4).
- [x] `$tables` приведён к реальным таблицам (`buy_product_prices`, `sales_products_prices`; убрана
  `service_categories`).
- [x] Удалён `#region agent log` из `SyncController.php`.
