# Среды и выкат: dev-VPS и prod-VPS

> Канонический документ по контурам (задача **11.1**): всё, что нужно, чтобы выкатить и проверить
> и dev, и prod, не читая историю коммитов и TODO.
> Связанные документы: [`API.md`](API.md) (контракт), [`DB.md`](DB.md) (схема),
> `../README.md` (быстрый старт Docker), трекер фаз — `ledger-craft-offline-first-PS/TODO.md` (Фаза 11).

## 1. Контуры

| | **dev-VPS** | **prod-VPS** |
|---|---|---|
| Домен | `dev.medovf2h.beget.tech` | `<prod-домен>` — **ещё не выбран** (задача 11.12) |
| IP | `217.114.0.27` (хост `mbmpuqvzic`) | — |
| Назначение | обкатка новых фич, миграций и синка | боевой контур мастерских |
| Данные | песочница: БД не жалко (том можно снести) | реальные данные: только с бэкапом |
| Кто обновляет | свободно, в любой момент | только проверенное на dev, по чек-листу |
| БД | Postgres, том `./tmp/db` (`ledger_craft_db`) | отдельный том на своей машине |
| Клиент | запускается **локально** (`npm run dev` → `http://localhost:9000`) | собранный `dist/spa` на хосте контура |

**Правило:** фича сначала проверяется на **dev** (чек-лист — трекер `ledger-craft-offline-first-PS/TODO.md`,
Фаза 11), и только потом
уходит на **prod**. Прямая выкладка новых фич в бой не делается.

## 2. Где прописан домен контура

| Место | Файл / переменная | Комментарий |
|---|---|---|
| Домен приложения | `docker-compose.yaml` → метка `traefik.http.routers.nginx.rule=Host(\`…\`)` | на каждом контуре своё значение |
| URL приложения (Laravel) | `docker-compose.yaml` → `environment.APP_URL` (или `.env`) | влияет на генерируемые ссылки (share-ссылка, письма) |
| Адрес API у клиента | репозиторий фронта → `VITE_API_URL` (`.env`, `.env.local`, `.env.prod`) | нигде не зашит в код (задача 7.1) |
| Разрешённые origins | `config/cors.php` → `allowed_origins` | `http://localhost:9000`, `:9001` (dev-SPA) + `https://localhost`, `capacitor://localhost` (мобильный клиент Capacitor) |
| Форсированный https | `app/Providers/AppServiceProvider.php` → `URL::forceScheme('https')` | поэтому все ссылки всегда `https://` |

⚠️ **CORS — причина №1 «клиент не ходит на API».** Если клиент открывается с origin, которого нет
в `allowed_origins` (другой порт/хост, размещённый SPA) — сначала добавьте этот origin в
`config/cors.php`, иначе запросы блокирует браузер ещё до Laravel (в консоли CORS-ошибка,
на сервере — тишина).

📱 **Мобильный клиент (Capacitor).** WebView отдаёт origin `https://localhost` (`androidScheme`
по умолчанию `https`), поэтому этот origin обязан быть в списке. Симптом без него очень
характерный: на телефоне с рабочим интернетом регистрация/вход показывают «Нет связи с сервером —
для регистрации нужен интернет» (axios не получает ответа), а в логах сервера **нет** запроса —
preflight `OPTIONS /api/register` приходит и уходит с `204` **без** `Access-Control-Allow-Origin`.
Проверка из терминала:

```bash
curl -s -o /dev/null -D - -X OPTIONS -H 'Origin: https://localhost' \
  -H 'Access-Control-Request-Method: POST' https://dev.medovf2h.beget.tech/api/register \
  | grep -i access-control-allow-origin     # должно быть: access-control-allow-origin: https://localhost
```


## 3. Доступ к dev-VPS

```bash
# на рабочей машине (в ~/.ssh/config: Host dev-vps, User root, IdentityFile ~/.ssh/id_ed25519)
ssh dev-vps
```

- ключ **без парольной фразы** → команды работают неинтерактивно (в т.ч. из скриптов/агентов);
- host key домена сверен с уже доверенным ключом IP (обе записи в `known_hosts`);
- переустановили VPS → `ssh-keygen -R 217.114.0.27` (иначе `REMOTE HOST IDENTIFICATION CHANGED`);
- **prod так не подключаем**: на боевом контуре — только ручные действия.

Что где лежит на машине контура:

```
/var/www/LedgerCraftDocker03/     # код (рабочая копия репозитория)
  .env                            # не в git: APP_KEY и т.п. (compose задаёт свои значения)
  tmp/db/                         # том Postgres (данные)
  letsencrypt/acme.json           # TLS-сертификаты Traefik — НЕ удалять (лимиты Let's Encrypt)
  public/build/                   # ассеты web-части (@vite): в .gitignore, собираются на сервере
```

## 4. Выкат: dev (чистая переустановка)

Проверено 12.09.2026 (TODO 11.4). Данные песочницы стираются — это и нужно.

```bash
# 0) страховка перед сносом
docker exec ledger_craft_db pg_dump -U root -d ledger_craft_db > /root/backup_$(date +%F_%H%M).sql
cp .env /root/env.backup_$(date +%F_%H%M)

# 1) снести контейнеры и том БД
docker compose down && rm -rf tmp/db

# 2) привести код ровно к origin/master (сохранив .env, TLS и том)
git fetch origin && git reset --hard origin/master
git clean -fdx -e .env -e letsencrypt -e tmp

# 3) собрать и поднять
docker image prune -f                    # при ~2 ГБ свободного места это не роскошь
docker compose up -d --build --remove-orphans
docker compose restart traefik           # ⚠️ иначе домен отдаёт 404 — см. §7

# 4) зависимости, права, схема
docker exec ledger_craft_app php composer.phar install --no-dev --optimize-autoloader
chmod -R 777 storage bootstrap/cache
docker exec ledger_craft_app php artisan migrate --force
docker exec ledger_craft_app php artisan db:seed --class=SpecializationTemplateSeeder --force   # пресеты специализаций (11.3)
docker exec ledger_craft_app php artisan config:clear
docker exec ledger_craft_app php artisan route:clear

# 5) ассеты web-части (@vite в resources/views/layouts/app.blade.php):
#    без них /login → 500 «Vite manifest not found»
docker run --rm -v "$PWD":/app -w /app node:20-alpine sh -c "npm ci --no-audit --no-fund && npm run build"
docker exec ledger_craft_nginx nginx -s reload
```

## 5. Выкат: prod (обновление без сноса)

Отличия: **никаких** `down -v`, `rm -rf tmp/db`, `git clean`; обязательный дамп до выката.

```bash
pg_dump ... > /root/backup_before_$(date +%F_%H%M).sql    # бэкап ДО
git pull --ff-only
docker compose up -d --build
docker compose restart traefik
docker exec ledger_craft_app php composer.phar install --no-dev --optimize-autoloader
docker exec ledger_craft_app php artisan migrate --force
docker exec ledger_craft_app php artisan db:seed --class=SpecializationTemplateSeeder --force   # пресеты специализаций (11.3)
docker exec ledger_craft_app php artisan config:clear
docker exec ledger_craft_app php artisan route:clear
docker run --rm -v "$PWD":/app -w /app node:20-alpine sh -c "npm ci --no-audit --no-fund && npm run build"
docker exec ledger_craft_nginx nginx -s reload
```

**Откат:** вернуть предыдущий коммит (`git checkout <tag|commit>`), при необходимости
`php artisan migrate:rollback` для последней партии (или восстановить дамп), затем `up -d`,
`nginx -s reload`.

## 6. Smoke после любого выката

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/            # 302 (гость → /login)
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/login       # 200 (web-часть жива)
curl -s -o /dev/null -w '%{http_code}\n' -X POST https://<домен>/api/sync     # 401 (нужен токен)
curl -s -o /dev/null -w '%{http_code}\n' -X POST https://<домен>/api/register # 422 (валидация)
docker exec ledger_craft_app php artisan route:list | grep -E 'sync|register|login|specialization-templates'
docker exec ledger_craft_app php artisan migrate:status | grep -c Pending      # 0
docker exec ledger_craft_db psql -U root -d ledger_craft_db -t -c \
  "SELECT count(*) FROM specialization_templates;"                             # 4 пресета (11.3)
```

Живой прогон синка (Фаза 11.5): регистрация → `specializations` с `user_id`; `POST /api/sync`
вставляет `categories`, затем `services` с FK = **серверный** id родителя; `/sync-updates` отдаёт
запись другому устройству и **не** отдаёт автору (анти-эхо).

## 7. Грабли, найденные на живом dev-контуре (12.09.2026)

- **Домен отдаёт 404, хотя все контейнеры Up.** Traefik не всегда подхватывает контейнеры,
  стартовавшие *после* него: в его `/api/http/routers` нет `nginx@docker`
  (проверка: `curl -s localhost:8080/api/http/routers`). Лечится `docker compose restart traefik`.
- **`GET /` → 403 «directory index is forbidden».** В `nginx.conf` не было `index index.php;`
  (`try_files $uri $uri/ …` упирался в каталог). Исправлено коммитом `ed43eb0`.
- **`/login` → 500 «Vite manifest not found: /var/www/public/build/manifest.json».** Blade
  использует `@vite(...)`, а `public/build` в `.gitignore` → ассеты надо собирать на сервере
  (шаг 5 выше). В образе `app` Node 16, а Vite 5 требует Node ≥ 18 — поэтому сборка идёт в
  одноразовом `node:20-alpine`.
- **Сотни «изменённых» файлов в `git status`** (после выкатов копированием файлов) при том, что
  история на месте: `git reset --hard` + `git clean` с исключениями `.env`, `letsencrypt`, `tmp`
  безопасны.
- **Ответ приложения на `/api/sync` без токена — `401`, а не `500`:** с 3.10 синк под `auth:sanctum`.
  Клиент без токена вообще не ходит в сеть (индикатор показывает «требуется вход»).

## 8. Что нужно, чтобы поднять prod (задача 11.12)

- [ ] домен боевого контура + A-запись на IP prod-VPS;
- [ ] копия `docker-compose.yaml` с **своими** `Host(...)` и `APP_URL`, отдельный том `tmp/db`,
      свой `APP_KEY` и пароли БД (не dev-значения из файла);
- [ ] `.env` на машине контура (not in git) + `letsencrypt/` для TLS;
- [ ] бэкапы БД по расписанию и **дамп перед каждым выкатом**;
- [ ] проверить CORS: если SPA будет размещён на домене контура — добавить его origin;
- [ ] `migrate --force` + `db:seed --class=SpecializationTemplateSeeder --force` (пресеты, 11.3),
      сборка ассетов web-части, smoke (см. §6), описанный план откатa;
- [ ] на prod **не** запускать `down -v` / `git clean` (данные и `.env` там свои).

## 9. Связь с клиентом

Клиент (`ledger-craft-offline-first-PS`) нигде не хранит адрес сервера в коде: он берёт
`VITE_API_URL` из env-файла (задача 7.1). Поэтому «переключить контур» = собрать/запустить клиент
с нужным `VITE_API_URL`; подробности — `ledger-craft-offline-first-PS/README.md` §«Среды и выкат».
На dev клиент удобнее всего держать запущенным локально (`npm run dev` → `localhost:9000`), потому
что этот origin уже разрешён в `config/cors.php`.

