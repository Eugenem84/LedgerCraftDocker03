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
- `localId = payload.uuid_id ?? payload.local_id ?? op.id`;
- из payload **вырезаются** `id`, `local_id`, `uuid_id` перед вставкой;
- `errors`:
  - `Invalid operation structure or table.` — неизвестная таблица / битая структура;
  - `MISSING_ID_FOR_UPDATE` / `MISSING_ID_FOR_DELETE` — нет `id` (серверного);
  - `DATABASE_ERROR` / `GENERAL_ERROR` — с `details`;
- ⚠️ исключения ловятся **внутри** цикла → `DB::transaction` не откатывает частично
  применённые операции (риск);
- ⚠️ `last_sync_id` (анти-эхо) проставляется только если колонка существует — а её нет.

### Спец-обработка таблиц

- **`orders`**: принимаются только `specialization_id, client_id, hours, minutes, total_amount, comments`
  (остальные поля при insert теряются). `total_amount` — **в рублях**, без конверсии.
- **`order_service`**: ждёт `order_id`/`service_id` уже как **серверные** ID, `sale_price`,
  `quantity`; если `sale_price` пуст — берётся `price` из `services`.

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
- анти-эхо (`last_sync_id`) не действует — колонок нет;
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

- [ ] **Идемпотентность**: `insertGetId` без проверки дублей; `uuid_id` есть только у `order_service`.
- [ ] **`last_sync_id`**: колонок нет → анти-эхо не работает.
- [ ] **Soft-delete**: `tableHasSoftDeletes()` знает только `clients, products, services, categories`.
- [ ] **Транзакция `/sync`**: ошибки «съедаются», нет частичного отката.
- [ ] **`orders` в синке**: теряются `status`, `paid`, `model_id`, `share_token`.
- [x] Исправлены `$tables` (`buy_product_prices`, `sales_products_prices`; убран `service_categories`).
- [x] Удалён `#region agent log` из `SyncController.php`.
