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
| Веб-сервер | nginx проекта + reverse proxy контура: дома — общий **Caddy**, у прежнего VPS — Traefik (TLS; домены контуров — см. «Среды: домашний сервер и prod») |
| Синхронизация | `SyncController` (PHP) — **единственный транспорт** (`/api/sync`, `/api/sync-updates`), решение D1 |
| Инфраструктура | `docker-compose.yaml` |

## Быстрый старт (Docker)

> ⚠️ `docker-compose.yaml` в этом репозитории — **контур прежнего dev-VPS** (Traefik + nginx + app +
> db, публикует 80/443/5433). Текущий dev-контур живёт на **домашнем сервере** и собирается из
> инфра-репозитория `home-server-vps`: `projects/ledgercraft/compose.yml` (порты наружу не публикуются,
> TLS терминирует общий Caddy). Регламент — [`DEPLOY.md`](DEPLOY.md).

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
docker exec -it ledger_craft_app php artisan db:seed --class=SpecializationTemplateSeeder
```

> Пресеты специализаций (`specialization_templates`, Фаза 11/11.3) — это **контент**, не данные
> пользователя: без них новый пользователь не получит стартовый каталог своей ниши
> (`GET /api/specialization-templates` отдаёт 4 пресета — `bike`/`aquarium`/`hvac`/`auto`).
> Сид идемпотентен, поэтому на контурах его можно запускать повторно (`--force`), а правка
> контента приезжает клиенту без релиза приложения (10.7).

> ⚠️ `docker-compose.yaml` содержит dev-секреты прямо в файле (APP_KEY, пароли БД) —
> для продакшена вынести в `.env`, не хранить в репозитории.
> ⚠️ В `.env.example` указан `DB_CONNECTION=mysql`, а рабочий docker использует `pgsql` —
> привести к единому.

## Среды: домашний сервер (dev) и prod

Проект живёт на **двух площадках**, и они не взаимозаменяемы (с **17.09.2026** dev-контур переехал
с Beget-VPS на домашний сервер — подробности и таблица «было → стало» в
[`docs/ENVIRONMENTS.md`](docs/ENVIRONMENTS.md) §1.1):

| Контур | Домен | Назначение | Данные |
|---|---|---|---|
| **dev (домашний сервер)** | `ledgercraft.dev.medovf2h.beget.tech` | обкатка новых фич, миграций и синка | песочница: БД не жалко (том можно снести) |
| **prod** | `<prod-домен>` (ещё не выбран — задача 11.12) | боевой контур мастерских | только с бэкапом, обновление по чек-листу |
| ~~прежний dev-VPS~~ | `dev.medovf2h.beget.tech` | страховка/откат 2–4 недели после переезда | живёт как есть; ничего не удаляем |

📖 **Полная процедура — [`docs/ENVIRONMENTS.md`](docs/ENVIRONMENTS.md):** где прописан домен
(Caddyfile дома / Traefik у прежнего VPS, `APP_URL`, `VITE_API_URL`), CORS и почему он «роняет» клиент,
доступ к контурам по SSH, проверенные команды выката, smoke, «грабли» живого контура (включая
найденные при переезде) и чек-лист поднятия prod.
📋 **Регламент проекта на домашнем сервере — [`DEPLOY.md`](DEPLOY.md)** (пути, права `.env`, релизы, бэкап).

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
- [`docs/ENVIRONMENTS.md`](docs/ENVIRONMENTS.md) — **среды и выкат**: домашний контур (dev),
  прежний dev-VPS как страховка, prod-чек-лист, команды выката, smoke, «грабли», переезд 17.09.2026.
- [`DEPLOY.md`](DEPLOY.md) — регламент проекта на домашнем сервере (пути, права `.env`, деплой,
  промо, релизы APK/OTA, бэкап).

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
