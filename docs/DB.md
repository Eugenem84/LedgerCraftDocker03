# DB: серверная схема (PostgreSQL)

> Полные колонки и история изменений — в `database/migrations/` (53 миграции).
> Здесь — карта таблиц, назначение и важные нюансы. Локальная схема клиента описана
> в репозитории фронта (`docs/DATA-MODEL.md`) и отличается.

## Синхронизируемые таблицы

| Таблица | Назначение | Ключевые колонки / нюансы |
|---|---|---|
| `specializations` | специализации мастерской | `name` (на сервере `specialization_name` → клиент переименовывает) |
| `clients` | клиенты | `name`, `phone`, `specialization_id`, `deleted_at` (soft) |
| `categories` | категории работ | `category_name`, `specialization_id` |
| `services` | работы (услуги) | `service`, `price` (**string!**), `category_id`, `deleted_at` |
| `product_categories` | категории товаров | `specialization_id`, связь с товарами |
| `products` | товары (склад) | `name`, `manufacturer`, `product_number`, `weight` (decimal), `base_sale_price` (int), `deleted_at` |
| `product_stocks` | остатки на складе | `product_id` (FK), `quantity`, `supplier` (nullable) |
| `buy_product_prices` | закупочные цены | `product_id` (FK), `buy_price` (int) |
| `sales_products_prices` | цены продажи по заказам | `product_id`, `order_id`, `sale_price` (int) |
| `incoming_products` | приходы товара | `product_id` (FK), `supplier` (string), `quantity`, **`by_price`** (int) |
| `orders` | заказы | `specialization_id`, `client_id`, `hours`, `minutes`, `total_amount` (**рубли**, int), `comments`, `status`, `paid`, `model_id`, `user_order_number`, `share_token`; колонка `materials` удалена |
| `order_service` | связка заказ↔работа | `order_id`, `service_id`, `sale_price`, `quantity`, `uuid_id` (миграция 2026), timestamps; **без PK** (композитный ключ закомментирован) |
| `order_product` | связка заказ↔товар | `order_id`, `product_id`, `sale_price`, `quantity` |
| `materials` | материалы | `name`, `specialization_id`, `deleted_at` |
| `equipment_models` | модели техники | `name`, `specialization_id`, `deleted_at` |

## Служебные таблицы

`users`, `personal_access_tokens` (Sanctum), `password_reset_tokens`, `password_resets`,
`failed_jobs`, `migrations`.

## Нюансы

- **Деньги** — целые числа в **рублях** (совпадает с клиентом после задачи 2.3).
- **Soft-delete** (`deleted_at`) есть не у всех таблиц; серверный `SyncController`
  учитывает только `clients, products, services, categories`.
- **`uuid_id`** — только у `order_service` (для сопоставления с клиентом). У остальных
  таблиц сопоставление с клиентом идёт по `server_id` на стороне клиента.
- **`last_sync_id`** — задумывался для анти-эха, но колонок нет ни у одной таблицы.
- **`order_service`** не имеет собственного PK и timestamps изначально; `updated_at`/`uuid_id`
  добавлены отдельными миграциями.
- `services.price` — строковый тип (историческое), стоит привести к числу.

## Экспериментальный Go-сайдкар

В `sync/` (модуль `ledgercraft/sync`) — отдельный сервис на Go с `POST /sync`,
работающий напрямую с Postgres через `pgx`. Запускается контейнером `sync` (порт `:8081`).
Назначение — альтернативная реализация синхронизации; сейчас не интегрирован с Laravel-роутами.
