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

| Контур | Домен | Назначение | БД |
|---|---|---|---|
| **dev-VPS** | `dev.medovf2h.beget.tech` | обкатка новых фич и миграций; БД — песочница, том не жалко | `ledger_craft_db` (том `./tmp/db`) |
| **prod-VPS** | `<prod-домен>` (уточняется — TODO 11.1) | боевой контур с реальными данными мастерских | отдельный том; бэкап обязателен |

Где что лежит и как выкатывать:

- код — рабочая копия этого репозитория на машине контура, запускается через `docker-compose.yaml`
  (`traefik` + `nginx` + `app` (php-fpm) + `db`); домен задаётся Traefik-меткой
  `traefik.http.routers.nginx.rule=Host(...)` и переменной `APP_URL`;
- БД — том `./tmp/db` (Postgres); TLS-сертификаты — `./letsencrypt/acme.json`
  (**не удалять**: упрётесь в лимиты Let's Encrypt);
- миграции — `docker exec -it ledger_craft_app php artisan migrate --force`
  (на prod **без** `down -v` и с бэкапом до выката);
- клиентская статика (`dist/spa` из репозитория фронта) выкладывается на хост контура;
  адрес API у клиента задаётся `VITE_API_URL` (см. `ledger-craft-offline-first-PS/README.md`
  §«Среды и выкат»).

```bash
# dev: чистая переустановка (данные песочницы стираются — это и нужно)
docker compose down -v && rm -rf tmp/db && git pull
docker compose up -d --build --remove-orphans      # уберёт осиротевшие сервисы (напр. Go-сайдкар)
docker exec -it ledger_craft_app php artisan migrate --force
docker exec -it ledger_craft_app php artisan config:clear
docker exec -it ledger_craft_app php artisan route:clear

# prod: только обновление, БД сохраняем
pg_dump ... > backup_before_release.sql            # бэкап ДО выката
git pull && docker compose up -d --build
docker exec -it ledger_craft_app php artisan migrate --force
docker exec -it ledger_craft_app php artisan config:clear
docker exec -it ledger_craft_app php artisan route:clear
```

**Правило:** фича сначала проверяется на **dev** (чек-лист — `TODO.md`, Фаза 11), и только потом
уходит на **prod**. Новые фичи прямой выкладкой в бой не отправляем.

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
_docker/                  # Dockerfile'ы (app, nginx), php.ini
tests/                    # PHPUnit
```

> Go-сайдкар синхронизации (`sync/`, `_docker/sync/`, сервис `sync` в compose) вынесен из
> проекта в песочницу `../ledger-craft-go-sync-sandbox` — решение D1, задача 3.11.

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
