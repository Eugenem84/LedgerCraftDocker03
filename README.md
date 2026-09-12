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
pg_dump ... > /root/backup_$(date +%F_%H%M).sql     # страховка, если данные ещё нужны
cp .env /root/env.backup_$(date +%F_%H%M)           # .env не в git
docker compose down && rm -rf tmp/db                # том Postgres сносим, сеть/контейнеры тоже
git fetch origin && git reset --hard origin/master  # локальные правки на VPS не нужны
git clean -fdx -e .env -e letsencrypt -e tmp        # сохраняем .env, TLS-сертификаты и том БД
docker image prune -f                               # освобождаем место (на dev-диске его мало)
docker compose up -d --build --remove-orphans
docker compose restart traefik                      # ⚠️ иначе домен отдаёт 404 — см. «Грабли»
docker exec ledger_craft_app php composer.phar install --no-dev --optimize-autoloader
chmod -R 777 storage bootstrap/cache
docker exec ledger_craft_app php artisan migrate --force
docker exec ledger_craft_app php artisan config:clear
docker exec ledger_craft_app php artisan route:clear
# ассеты web-части (@vite в resources/views/layouts/app.blade.php): без них /login → 500
docker run --rm -v "$PWD":/app -w /app node:20-alpine sh -c "npm ci --no-audit --no-fund && npm run build"
docker exec ledger_craft_nginx nginx -s reload

# prod: только обновление, БД сохраняем (никаких down -v и git clean)
pg_dump ... > backup_before_release.sql             # бэкап ДО выката
git pull --ff-only && docker compose up -d --build
docker exec ledger_craft_app php composer.phar install --no-dev --optimize-autoloader
docker compose restart traefik
docker exec ledger_craft_app php artisan migrate --force
docker exec ledger_craft_app php artisan config:clear
docker exec ledger_craft_app php artisan route:clear
docker run --rm -v "$PWD":/app -w /app node:20-alpine sh -c "npm ci --no-audit --no-fund && npm run build"
docker exec ledger_craft_nginx nginx -s reload
```

Smoke после выката (так проверяли dev 12.09.2026):

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/            # 302 (гость → /login)
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/login       # 200 (web-часть жива)
curl -s -o /dev/null -w '%{http_code}\n' -X POST https://<домен>/api/sync     # 401 (нужен токен)
curl -s -o /dev/null -w '%{http_code}\n' -X POST https://<домен>/api/register # 422 (валидация)
docker exec ledger_craft_app php artisan route:list | grep -E 'sync|register|login|specialization-templates'
docker exec ledger_craft_app php artisan migrate:status | grep -c Pending      # 0
```

### Грабли, найденные на живом dev-контуре (12.09.2026)

- **Домен отдаёт 404, хотя контейнеры Up.** Traefik подхватывает контейнеры, стартовавшие
  *после* него, не всегда: в его `/api/http/routers` нет `nginx@docker` (проверяется
  `curl -s localhost:8080/api/http/routers`). Лечится `docker compose restart traefik`.
- **`GET /` → 403 «directory index is forbidden».** В `nginx.conf` не было `index index.php;`
  (`try_files $uri $uri/ ...` упирался в каталог). Исправлено коммитом `ed43eb0`.
- **`/login` → 500 «Vite manifest not found at: /var/www/public/build/manifest.json».**
  Blade-шаблон использует `@vite(...)`, а `public/build` в `.gitignore` → ассеты надо собирать
  на сервере (команда выше). В образе `app` стоит Node 16, а Vite 5 требует Node ≥ 18,
  поэтому сборка вынесена в одноразовый `node:20-alpine`.
- **222 «изменённых» файла в `git status`** после выкатов «копированием файлов» — история при этом
  остаётся на месте, но рабочее дерево грязное; `git reset --hard` + `git clean` (с исключениями)
  приводят копию к `origin/master` и это безопасно, т.к. `.env`, `letsencrypt/` и том БД сохраняются.

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
