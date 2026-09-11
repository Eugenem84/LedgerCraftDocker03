# Ledger Craft — Backend (Laravel API)

Серверная часть системы учёта работ и запчастей для мастерских. Обслуживает
**offline-first** приложение (клиент — Quasar/Vue + локальный SQLite/sql.js):
принимает пакеты локальных изменений, отдаёт инкрементальные обновления, ведёт склад,
приходы товара, цены и статистику.

> Связанный репозиторий (клиент): `ledger-craft-offline-first-PS` (Quasar/Vue).
> Оба открываются в одном `.code-workspace`.

## Стек

| Слой | Технология |
|---|---|
| Язык / фреймворк | PHP 8.2 + Laravel |
| БД | **PostgreSQL** (в `docker-compose`) |
| Авторизация | Laravel Sanctum (токены) |
| Идентификация устройства | заголовок `X-Sync-ID` (UUID клиента) |
| Веб-сервер | nginx + Traefik (TLS, dev-домен `dev.medovf2h.beget.tech`) |
| Синхронизация | `SyncController` (PHP); **экспериментальный Go-сайдкар** `sync/` |
| Инфраструктура | `docker-compose.yaml` |

## Быстрый старт (Docker)

```bash
docker-compose up -d --build
```

Сервисы:

| Сервис | Контейнер | Наружу |
|---|---|---|
| `traefik` | `ledger_craft_traefik` | `:80`, `:443`, дашборд `:8080` |
| `nginx` | `ledger_craft_nginx` | через Traefik (домен `dev.medovf2h.beget.tech`) |
| `app` (php-fpm) | `ledger_craft_app` | `:5173` (dev-сервер) |
| `sync` (Go) | `ledger_craft_sync` | `:8081` → контейнерный `:8080` |
| `db` (Postgres) | `ledger_craft_db` | `:5433` → контейнерный `:5432` |

Миграции и ключ приложения:

```bash
docker exec -it ledger_craft_app php artisan migrate
docker exec -it ledger_craft_app php artisan key:generate
```

> ⚠️ `docker-compose.yaml` содержит dev-секреты прямо в файле (APP_KEY, пароли БД) —
> для продакшена вынести в `.env`, не хранить в репозитории.
> ⚠️ В `.env.example` указан `DB_CONNECTION=mysql`, а рабочий docker использует `pgsql` —
> привести к единому.

## Конфигурация

- `.env.example` — шаблон окружения; рабочий `.env` не коммитится.
- `env.production` — прод-переменные.
- `config/` — стандартные конфиги Laravel.

## Структура проекта

```
app/
├── Http/Controllers/     # API-контроллеры (SyncController, ProductController, OrderController, …)
└── Repositories/         # слой доступа к данным (ProductRepository, IncomingProductRepository, …)
routes/
└── api.php               # все API-роуты
database/
└── migrations/           # 53 миграции схемы БД
sync/                     # Go-сайдкар синхронизации (экспериментальный)
_docker/                  # Dockerfile'ы (app, nginx, sync), php.ini
tests/                    # PHPUnit
```

## Документация

- [`docs/API.md`](docs/API.md) — **канонический контракт API** (роуты синка, приход, склад, отчёты).
- [`docs/DB.md`](docs/DB.md) — серверная схема БД (таблицы, назначение, статус).

## Основные API-роуты

Полный список — в `routes/api.php` и `docs/API.md`. Ключевые:

| Метод | Путь | Назначение |
|---|---|---|
| POST | `/api/sync` | приём пакета локальных изменений клиента |
| GET | `/api/sync-updates` | инкрементальная выдача изменений |
| POST | `/api/arrival_product` | приход товара на склад |
| POST | `/api/register`, `/api/login` | авторизация (Sanctum) |
| GET | `/api/get_product_stocks/{productCategoryId}` | остатки склада |
| GET | `/api/orders_by_specialization/{id}` | заказы по специализации |
| GET | `/api/get_total_DWYM/{specializationId}` | статистика |

## Тесты

```bash
docker exec -it ledger_craft_app php artisan test
# или
docker exec -it ledger_craft_app ./vendor/bin/phpunit
```
