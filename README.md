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
| Веб-сервер | nginx + Traefik (TLS; домены контуров — см. «Среды: dev-VPS и prod-VPS») |
| Синхронизация | `SyncController` (PHP) — **единственный транспорт** (`/api/sync`, `/api/sync-updates`), решение D1 |
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

## Среды: dev-VPS и prod-VPS

Проект живёт на **двух площадках** (с 12.09.2026), и они не взаимозаменяемы:

| Контур | Домен | Назначение | Данные |
|---|---|---|---|
| **dev-VPS** | `dev.medovf2h.beget.tech` | обкатка новых фич, миграций и синка | песочница: БД не жалко (том можно снести) |
| **prod-VPS** | `<prod-домен>` (ещё не выбран — задача 11.12) | боевой контур мастерских | только с бэкапом, обновление по чек-листу |

📖 **Полная процедура — [`docs/ENVIRONMENTS.md`](docs/ENVIRONMENTS.md):** где прописан домен
(Traefik `Host(...)`, `APP_URL`, `VITE_API_URL`), CORS и почему он «роняет» клиент, доступ к
dev-VPS по SSH-ключу, проверенные команды выката dev и prod, smoke, «грабли» живого контура и
чек-лист поднятия prod.

**Правило:** фича сначала проверяется на **dev** (чек-лист — трекер `ledger-craft-offline-first-PS/TODO.md`,
Фаза 11) и только потом уходит на **prod**.

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
└── migrations/           # 63 миграции схемы БД
_docker/                  # Dockerfile'ы (app, nginx), php.ini
tests/                    # PHPUnit
```

> Go-сайдкар синхронизации (`sync/`, `_docker/sync/`, сервис `sync` в compose) вынесен из
> проекта в песочницу `../ledger-craft-go-sync-sandbox` — решение D1, задача 3.11.

## Документация

- [`docs/API.md`](docs/API.md) — **канонический контракт API** (роуты синка, приход, склад, отчёты).
- [`docs/DB.md`](docs/DB.md) — серверная схема БД (таблицы, назначение, статус).
- [`docs/ENVIRONMENTS.md`](docs/ENVIRONMENTS.md) — **среды и выкат**: dev-VPS / prod-VPS, домены,
  CORS, доступ по SSH, команды выката, smoke, «грабли» и чек-лист поднятия prod (задача 11.1).

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
