# Среды и выкат: домашний сервер (бывший dev-VPS) и prod-VPS

> Канонический документ по контурам (задача **11.1**): всё, что нужно, чтобы выкатить и проверить
> и dev, и prod, не читая историю коммитов и TODO.
> Связанные документы: [`API.md`](API.md) (контракт), [`DB.md`](DB.md) (схема),
> `../README.md` (быстрый старт Docker), [`../DEPLOY.md`](../DEPLOY.md) (регламент выката),
> инфраструктура дома — репозиторий `home-server-vps` (`docs/09-ledgercraft.md`),
> трекер фаз — `ledger-craft-offline-first-PS/TODO.md` (Фаза 11).

## 1. Контуры

| | **dev (домашний сервер)** | **prod-VPS** |
|---|---|---|
| Домен | `ledgercraft.dev.medovf2h.beget.tech` (A → `90.156.169.123`) | `<prod-домен>` — **ещё не выбран** (задача 11.12) |
| Где живёт | домашний Ubuntu `192.168.2.207`, `/opt/projects/ledgercraft` (код — `backend/`) | — |
| Маршрут | интернет → VPS `90.156.169.123` (DNAT 80/443) → WireGuard-туннель → Caddy дома (`10.10.0.2:80` / `:8443`) → `ledgercraft-web:80` → php-fpm `ledgercraft-app:9000` | отдельный контур со своим доменом/БД |
| Назначение | обкатка новых фич, миграций и синка | боевой контур мастерских |
| Данные | песочница: БД не жалко (том можно снести) | реальные данные: только с бэкапом |
| Кто обновляет | свободно, в любой момент | только проверенное на dev, по чек-листу |
| БД | Postgres 17, том `ledgercraft_db_data` (наружу не публикуется) | отдельный том на своей машине |
| TLS | Caddy дома, Let's Encrypt (HTTP-01 через внешний `:80`) | Caddy/аналог на своей машине |
| Клиент | запускается **локально** (`npm run dev` → `http://localhost:9000`) | собранный клиент с prod-адресом |

**Прежний dev-VPS (`dev.medovf2h.beget.tech`, `217.114.0.27`) — страховка и откат** (см. §1.2):
контур с Traefik остановлен не был, его БД и `storage` остались нетронутыми.

**Правило:** фича сначала проверяется на **dev** (чек-лист — трекер `ledger-craft-offline-first-PS/TODO.md`,
Фаза 11), и только потом
уходит на **prod**. Прямая выкладка новых фич в бой не делается.

## 1.1. Переезд dev-контура на домашний сервер (17.09.2026)

Коротко: dev-контур переехал с Beget-VPS (`217.114.0.27`, Traefik) на домашний сервер
(`192.168.2.207`, общий Caddy). Код и образ остались теми же — отличается только окружение.
Канонические детали — `home-server-vps/docs/09-ledgercraft.md`.

| Что | Было (dev-VPS) | Стало (дома) |
|---|---|---|
| Прокси и TLS | Traefik (`0.0.0.0:80/443`), сертификаты у него | Caddy дома (`10.10.0.2:80` / `:8443`), TLS у Caddy; проект портов на хост **не публикует** |
| Compose | `docker-compose.yaml` в репозитории (traefik + nginx + app + db) | `/opt/projects/ledgercraft/compose.yml` (web + app + db, без traefik), код — git-клон в `backend/` |
| Каталог | `/var/www/LedgerCraftDocker03` | `/opt/projects/ledgercraft` (код — `backend/`) |
| `APP_URL` | `https://dev.medovf2h.beget.tech` | `https://ledgercraft.dev.medovf2h.beget.tech` |
| Домен в прокси | `Host(...)` в метках контейнера nginx | блок в `/opt/server/caddy/Caddyfile` |
| `APP_ENV` / `APP_DEBUG` | `production` из compose, но `APP_DEBUG=true` из `.env` | `APP_ENV=production`, `APP_DEBUG=false` |
| БД | Postgres 17.2, данные — в **анонимном томе** (`./tmp/db` монтировался не туда) | Postgres 17 (том `ledgercraft_db_data`), перенесено `pg_dump -Fc` → `pg_restore` — 8 users / 23 orders, `server_id` и sanctum-токены те же |
| Файлы релизов | `storage/app/public/{releases,bundles}` + legacy APK | перенесены потоком `tar` по SSH, sha256 всех файлов сверены |
| PHP для artisan | системный `/usr/bin/php` 8.2 на хосте | системного PHP с `pdo_pgsql` дома нет → `docker exec ledgercraft-app php …` |
| Промо | `https://dev.medovf2h.beget.tech/promo/` | `https://ledgercraft.dev.medovf2h.beget.tech/promo/` |

**Переключение устройств — OTA-бандлом, без установки APK** (решение 17.09.2026): один и тот же
бандл `1.14.10.260917-1812` (адрес API внутри — новый домен) опубликован **на оба контура**, чтобы
устройство не «прыгало» между ними: старый контур отдаёт его как обновление (устройство переезжает),
новый — как уже применённое. Порядок и откат — §1.2.

## 1.2. Откат переезда и вывод прежнего контура

- Прежний dev-VPS **не выключаем** 2–4 недели: он и внешний наблюдатель для проверок дома
  (как это уже принято для Locsy), и хранилище дампа/`.env`/`letsencrypt`.
- Откат устройств: опубликовать на домашнем контуре бандл с прежним адресом
  (`RELEASE_API_URL=https://dev.medovf2h.beget.tech/api`) — устройства вернутся на VPS.
- Полный откат БД: дамп `ledgercraft-dev-2026-09-17.dump` (дома `/opt/backups/pg`, та же копия
  снималась на VPS в `/root`) накатывается обратно на VPS.
- Вывод из эксплуатации — отдельным согласованным шагом: остановить контейнеры `ledger_craft_*`
  на VPS (том и дампы сохранить), не удаляя ничего «за компанию».

## 1.3. Внешние проверки — только с независимого хоста

С Mac (активный VPN) и с домашнего сервера (провайдер подменяет SYN-ACK на закрытых портах)
проверять «снаружи» нельзя. Независимая точка входа — **`dev-vps`**:

```bash
ssh dev-vps 'curl -sSI https://ledgercraft.dev.medovf2h.beget.tech/ | head -3'
ssh dev-vps 'curl -sS  https://ledgercraft.dev.medovf2h.beget.tech/api/app-version'
```

## 2. Где прописан домен контура

| Место | Файл / переменная | Комментарий |
|---|---|---|
| Домен приложения (маршрутизация) | домашний контур — `/opt/server/caddy/Caddyfile`, блок `ledgercraft.dev.medovf2h.beget.tech { reverse_proxy ledgercraft-web:80 }`; прежний VPS — метка `traefik.http.routers.nginx.rule=Host(\`…\`)` | на каждом контуре своё значение |
| Имя сайта в nginx проекта | `/opt/projects/ledgercraft/nginx/ledgercraft.conf` → `server_name` | только «своё» имя, TLS здесь не терминируется |
| URL приложения (Laravel) | `backend/.env` → `APP_URL` (на прежнем VPS — `environment.APP_URL` в compose) | влияет на генерируемые ссылки (share-ссылка, письма); в ответах `/api/app-version` ссылки строит `url()` от текущего домена |
| Адрес API у клиента | репозиторий фронта → `VITE_API_URL` (`.env`, `.env.local`, окружение релизного скрипта) | нигде не зашит в код (задача 7.1) |
| Разрешённые origins | `config/cors.php` → `allowed_origins` | `http://localhost:9000`, `:9001` (dev-SPA) + `https://localhost`, `capacitor://localhost` (мобильный клиент Capacitor); домен контура в списке **не нужен** — клиент не ходит с него в браузере |
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


## 3. Доступ к контурам

### Домашний сервер (текущий dev)

```bash
# в ~/.ssh/config на рабочей машине: Host ledgercraft-home (он же home-server) → 192.168.2.207
ssh ledgercraft-home
```

- ⚠️ **только из домашней сети**: домашний сервер не публикует SSH в интернет; выкат клиента и
  публикация релизов делаются «из дома»;
- ключ **без парольной фразы** → команды работают неинтерактивно (в т.ч. из скриптов/агентов);
- внутри — `sudo -n` без пароля (по нему работают `health.sh` и правки прав файлов).

Что где лежит:

```
/opt/projects/ledgercraft/          # каталог проекта (инфра-репозиторий home-server-vps)
  compose.yml                       # web + app + db, БЕЗ публикации портов
  .env                              # DB_* для контейнера БД: 600
  nginx/ledgercraft.conf            # nginx проекта (root /var/www/public)
  backend/                          # git-клон LedgerCraftDocker03
    .env                            # Laravel: APP_KEY, APP_URL, DB_*, FEEDBACK_PULL_TOKEN: 640 euegene:www-data
    public/build/                   # ассеты web-части (@vite), в .gitignore — собираются на сервере
    public/promo/                   # промо-страница (пишет publish-landing.sh)
    storage/app/public/releases/    # APK + releases.json
    storage/app/public/bundles/     # OTA-бандлы + bundles.json
    storage/app/framework, logs/    # пишет php-fpm (www-data)
/opt/server/caddy/Caddyfile         # общий reverse proxy дома (блок домена контура)
/opt/backups/pg/                    # ручные дампы БД (автобэкапов на контуре пока нет)
```

### Прежний dev-VPS (страховка/откат)

```bash
ssh dev-vps            # в ~/.ssh/config: Host dev-vps, User root, IdentityFile ~/.ssh/id_ed25519
```

- host key домена сверен с уже доверенным ключом IP (обе записи в `known_hosts`);
- переустановили VPS → `ssh-keygen -R 217.114.0.27` (иначе `REMOTE HOST IDENTIFICATION CHANGED`);
- **prod так не подключаем**: на боевом контуре — только ручные действия.

Что где лежит (до вывода из эксплуатации — не удалять):

```
/var/www/LedgerCraftDocker03/     # код (рабочая копия репозитория)
  .env                            # не в git: APP_KEY и т.п. (compose задаёт свои значения)
  letsencrypt/acme.json           # TLS-сертификаты Traefik — НЕ удалять (лимиты Let's Encrypt)
  public/build/                   # ассеты web-части (@vite): в .gitignore, собираются на сервере
  storage/app/public/{releases,bundles}   # APK, OTA-бандлы и их манифесты
  tmp/db/                         # ⚠️ пустой каталог: данные Postgres лежали в анонимном томе
```

## 4. Выкат: домашний контур (текущий dev)

Проверено 17.09.2026 (переезд). Данные домашнего контура — песочница, но дамп перед изменениями
схемы снимаем всегда: `docker compose exec -T ledgercraft-db pg_dump -U root -d ledger_craft_db -Fc > /opt/backups/pg/…`.

```bash
cd /opt/projects/ledgercraft/backend
git pull                                     # код — git-клон; выкат = pull + миграции
docker exec ledgercraft-app php artisan migrate --force
docker exec ledgercraft-app php artisan db:seed --class=SpecializationTemplateSeeder --force   # пресеты специализаций (11.3, идемпотентно)
docker exec ledgercraft-app php artisan config:clear
docker exec ledgercraft-app php artisan route:clear
```

Если менялся `_docker/app/Dockerfile` — пересобрать образ:

```bash
cd /opt/projects/ledgercraft && docker compose up -d --build ledgercraft-app
```

Ассеты web-части (`public/build` в `.gitignore`, в образе Node 16, а Vite требует ≥ 18 — собираем в
одноразовом контейнере):

```bash
cd /opt/projects/ledgercraft
docker run --rm -v "$PWD/backend":/app -w /app node:20-alpine sh -c "npm ci --no-audit --no-fund && npm run build"
```

Применить `Caddyfile` (только если менялся блок домена): `docker restart caddy` — при `admin off`
команда `caddy reload` недоступна (ADR-018 в инфра-репозитории).

**Полный регламент проекта (включая промо и релизы APK/OTA) — [`../DEPLOY.md`](../DEPLOY.md).**

## 4.1. Прежний dev-VPS: чистая переустановка (страховка, историческая справка)

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

Prod может жить и на том же домашнем сервере (второй домен, свой каталог
`/opt/projects/ledgercraft-prod`, своя БД и свой блок в Caddyfile), и на отдельном VPS.
Для домашнего варианта команды выката — как в §4 (только `git pull` + `migrate --force` + сборка
ассетов при необходимости), но с дампом **до** выката и без сноса тома. Ниже — вариант выката
на VPS с Traefik:

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

На домашнем контуре — готовый скрипт (домен, раздача APK/OTA, промо, CORS мобильного клиента,
расширения PHP, .env, объём данных, отсутствие опубликованных портов):

```bash
ssh ledgercraft-home 'bash /opt/projects/ledgercraft/smoke.sh'        # 10 проверок
ssh ledgercraft-home 'bash /opt/projects/ledgercraft/smoke.sh --local'  # напрямую в Caddy (быстро)
```

Вручную (любой контур; `<домен>` = `ledgercraft.dev.medovf2h.beget.tech`):

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/            # 302 (гость → /login)
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/login       # 200 (web-часть жива)
curl -s -o /dev/null -w '%{http_code}\n' https://<домен>/promo/      # 200 (промо-страница)
curl -s https://<домен>/api/app-version                              # versionCode/bundle + apkUrl этого домена
curl -s -o /dev/null -w '%{http_code}\n' -X POST https://<домен>/api/sync     # 401 (нужен токен)
docker exec ledgercraft-app php artisan route:list | grep -E 'sync|register|login|specialization-templates'
docker exec ledgercraft-app php artisan migrate:status | grep -c Pending      # 0
docker compose exec -T ledgercraft-db psql -U root -d ledger_craft_db -t -c \
  "SELECT count(*) FROM specialization_templates;"                             # 4 пресета (11.3)
```

Снаружи (с независимого хоста — §1.3):

```bash
ssh dev-vps 'curl -sSI https://ledgercraft.dev.medovf2h.beget.tech/ | head -3'
ssh dev-vps 'curl -sS  https://ledgercraft.dev.medovf2h.beget.tech/api/app-version'
```

Живой прогон синка (Фаза 11.5): регистрация → `specializations` с `user_id`; `POST /api/sync`
вставляет `categories`, затем `services` с FK = **серверный** id родителя; `/sync-updates` отдаёт
запись другому устройству и **не** отдаёт автору (анти-эхо).
Живой прогон переезда (17.09.2026): устройство на прежнем контуре получило OTA-бандл `1.14.10.260917-1812`
и без установки APK переключилось на домашний API; вход и данные сохранились (токены и `server_id`
совпадают — переносился дамп БД, а не пустая схема).

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
- **`/login` → 500 «No application encryption key has been specified»** при живой БД и правильном
  `backend/.env`: php-fpm в контейнере работает под `www-data` (uid 33), а `.env` лежал с правами `600`
  (владелец — пользователь хоста) и прочитать его было нельзя. Лечится правами `640` и группой
  `www-data` (`sudo -n chgrp 33 backend/.env`). Симптом-маркер: artisan из консоли работает (там root),
  а веб отдаёт 500 (см. §4 — на домашнем контуре это часть схемы).
- **`/promo/` → 403 после публикации промо**: `rsync` сохраняет права источника, а staging-каталог
  у `publish-landing.sh --api` создаётся через `mktemp -d` (режим `700`) — веб-сервер не может
  прочитать каталог. Скрипт теперь после выката выполняет `chmod -R a+rX` на сервере.
- **`docker exec -T …` не существует**: флаг `-T` есть только у `docker compose exec`; для контейнера
  по имени — `docker exec ledgercraft-app php …` (без `-T`).
- **`scp` с Mac на домашний сервер падает** (`scp: dest open … Failure`) — релизные скрипты переведены
  на `rsync` (как и `publish-landing.sh`); rsync есть и на VPS.
- **Том Postgres**: в прежнем `docker-compose.yaml` бинд указывал на `/var/lib/postgres`, а образ
  хранит данные в `/var/lib/postgresql/data` — поэтому `tmp/db` был пустым, а данные лежали в
  анонимном томе. Новый контур использует именованный том и **переносит БД только `pg_dump`**.
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

## 10. Публикация Android-релиза (Фаза 13)

Клиент самообновления (`/api/app-version` + `/api/download-apk`) читает **манифест релизов**:
`storage/app/public/releases/releases.json` рядом с APK. Руками в этот каталог ничего не кладём —
файл и манифест пишет команда.

```bash
# на машине разработчика (нужны JDK + Android SDK), из репозитория клиента:
#   src-capacitor/android/gradle.properties → APP_VERSION_CODE += 1, APP_VERSION_NAME=1.2
RELEASE_SERVER=dev-vps npm run release:android -- --notes "Чиним склад"
# (скрипт сам: сборка → npx cap sync → assembleRelease → apksigner verify → scp → app:publish-apk)

# то же вручную на контуре:
scp app-release.apk dev-vps:/var/www/LedgerCraftDocker03/storage/app/releases/
ssh dev-vps "cd /var/www/LedgerCraftDocker03 && php artisan app:publish-apk \
  storage/app/releases/app-release.apk --version-code=2 --version-name=1.1 --notes='Чиним склад'"

# проверка
curl -s https://dev.medovf2h.beget.tech/api/app-version
curl -sI https://dev.medovf2h.beget.tech/api/download-apk | grep -i x-apk
```

### OTA-бандлы: обновление веб-слоя без установки APK (Фаза 15)

Правки интерфейса и логики приезжают на устройство **без установки APK**: клиент скачивает zip
с веб-сборкой (~1,2 МБ), сверяет sha256 и применяет его при следующем запуске. Манифест бандлов —
`storage/app/public/bundles/bundles.json`, рядом лежат сами zip.

```bash
# на машине разработчика (из репозитория клиента): сборка UI → zip → sha256 → публикация
RELEASE_SERVER=dev-vps npm run release:web -- --channel dev --min-native-version 15 --notes "Правки склада"

# нативная правка + бандл из того же кода одной командой (после APP_VERSION_CODE += 1)
RELEASE_SERVER=dev-vps npm run release:android -- --channel dev --with-bundle --notes "Чиним склад"

# домашний контур: сервер, каталог и запуск artisan в контейнере (системного PHP с pdo_pgsql дома нет)
RELEASE_SERVER=ledgercraft-home \
RELEASE_REMOTE_DIR=/opt/projects/ledgercraft/backend \
RELEASE_REMOTE_PHP='docker exec ledgercraft-app php' \
RELEASE_API_URL=https://ledgercraft.dev.medovf2h.beget.tech/api \
  npm run release:web -- --channel dev --notes "Правки склада"

# вручную на контуре:
php artisan app:publish-bundle storage/app/bundles/bundle-1.14.260915-1318.zip \
  --bundle-version=1.14.260915-1318 --checksum=<sha256 в hex> --min-native-version=15 --notes='…'

# проверка
curl -s https://<домен>/api/app-version | grep -o '"bundle":{[^}]*}'
```

⚠️ **Переезд контура (17.09.2026):** при переносе контура один и тот же бандл публикуется
**на оба контура** (`app:publish-bundle` с тем же `--bundle-version` и `--checksum`). Иначе
устройство, переключившееся на новый API, увидит там более старый бандл и «откатит» веб-слой назад.
Проверка: `/api/app-version` обоих контуров отдаёт один `bundle.version` и один `bundle.checksum`.


Что важно:

- **Источник правды — `bundles.json`**, руками в каталог ничего не кладём: его пишет только команда
  (проверяет, что это zip с `index.html` в корне, и сама считает хэши).
- **`checksum` — sha256 в HEX**, а не base64: плагин OTA на устройстве считает sha256 скачанного zip
  и приводит его к hex, а base64 отвергает ошибкой `Checksum mismatch` (живой прогон 15.09.2026).
  Base64 лежит рядом полем `checksumBase64` — только для справки.
- **Опция называется `--bundle-version`, а не `--version`**: `--version` у Symfony Console
  глобальный — печатает версию фреймворка и выходит, не доходя до команды.
- **`minNativeVersionCode` — код сборки, для которой бандл собран**: клиент не предложит бандл ни на
  более старом APK (`native < min`), ни на более новом (`0 < min < native`) — во втором случае
  встроенный веб-слой APK уже не старее, и «обновление» откатило бы мастеру интерфейс.
- **Откат бандла — на стороне клиента**: если бандл не «оживёт» за `readyTimeout` (10 с),
  приложение вернётся к встроенному веб-слою; после установки нового APK Capacitor сбрасывает
  веб-слой сам (`Bridge.isNewBinary()`), а клиент снимает «применится при перезапуске».

Что важно:

- **`versionCode` только растёт** — Android не ставит APK с меньшим или равным значением.
  Откат «на предыдущую сборку» технически невозможен: выпускаем новую версию с исправлением.
- **Подпись.** APK подписывается ключом разработчика (`keystore.properties`, в git не хранится).
  Другой ключ → «Приложение не установлено». Ключ обязан лежать в двух местах (бэкап).
- **Команда проверяет, что это APK** (ZIP + `AndroidManifest.xml`) и считает sha256; при отсутствии
  подписи — предупреждает (неподписанный APK клиент не установит).
- **Права на каталог.** `storage/app/public/releases` должен быть доступен веб-серверу на чтение
  (и `storage/app/public` — стандартная публичная папка Laravel, симлинк `public/storage` уже нужен).
- **Порядок сред неизменен:** сначала dev-VPS (проверяем обновление на живом устройстве),
  затем prod (задача 11.12). Прямой выкладки APK в бой нет.
- **Кто раздаётся и чем проверяется (решение 14.09.2026).** Единственная полоса — **релизная**
  сборка: dev-контур получает релизный APK с dev-адресом, боевой — тот же код, пересобранный с
  боевым адресом. Debug-сборки не публикуются и на устройстве для проверок не гоняются (они нужны
  только для отладки: `chrome://inspect`, `adb logcat`, live-reload) — иначе цепочка самообновления
  впервые сработала бы у мастера. Адрес контура задаёт клиентский скрипт (иначе нельзя: сборка
  Capacitor всегда buildType=prod, и `.env.prod` не отличает контуры):
  ```bash
  RELEASE_SERVER=dev-vps npm run release:android -- --channel dev    # сборка + проверка адреса в APK
  RELEASE_SERVER=prod-vps RELEASE_REMOTE_DIR=/var/www/<prod-репо> \
    RELEASE_API_URL=https://<prod-домен>/api \
    npm run release:android -- --channel prod
  ```
  Манифест релизов на каждом контуре свой (`releases.json`), поэтому контуры не мешают друг другу;
  `versionCode` обязан расти **внутри** контура — скрипт отказывает, если он не выше опубликованного
  (Android не поставит APK «вниз»). Промо-страница выкатывается с адресом своего контура:
  `publish-landing.sh --server … --url … --api https://<домен>/api`.
  ⚠️ prod-VPS пока не поднят (§8) — prod-путь в клиенте заготовлен и включается вместе с контуром
  (трекер: задача **13.17**).
- **Клиент без Play:** система покажет диалог подтверждения установки — это нормальное поведение
  Android; «тихая» установка возможна только для Play или Device Owner (MDM).

