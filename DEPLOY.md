# DEPLOY — Ledger Craft (ledgercraft.dev.medovf2h.beget.tech)

Регламент проекта в инфраструктуре домашнего сервера. Контуры и вся инфраструктура стенда —
репозиторий `home-server-vps` (`docs/09-ledgercraft.md`); среды контуров — [`docs/ENVIRONMENTS.md`](docs/ENVIRONMENTS.md).
Тот же файл лежит в инфра-репозитории: `projects/ledgercraft/DEPLOY.md`.

## Где живёт

- Домен: `ledgercraft.dev.medovf2h.beget.tech` (A → `90.156.169.123`).
- Сервер: домашний Ubuntu `192.168.2.207`; каталог проекта `/opt/projects/ledgercraft`,
  код бэкенда — `/opt/projects/ledgercraft/backend` (git-клон `LedgerCraftDocker03`, ветка `master`),
  монтируется в контейнер `ledgercraft-app` как `/var/www`.
- Путь запроса: интернет → VPS `90.156.169.123` (DNAT 80/443, SNAT, MSS-clamp) → WireGuard-туннель
  (`10.10.0.1` ↔ `10.10.0.2`) → Caddy дома (`10.10.0.2:80` / `:8443`, TLS Let's Encrypt) →
  `infra_net` → `ledgercraft-web:80` → php-fpm `ledgercraft-app:9000`.
- TLS терминирует Caddy; nginx проекта передаёт `X-Forwarded-Proto`, поэтому Laravel генерирует
  `https`-ссылки (`URL::forceScheme('https')` в `AppServiceProvider`).
- PostgreSQL: контейнер `ledgercraft-db` (`postgres:17-alpine`) в сети `ledgercraft_net`,
  наружу **не публикуется**.
- Промо-страница: `/promo/` (файлы в `backend/public/promo`).
- Раздача APK и OTA-бандлов: `/api/app-version`, `/api/download-apk`, `/api/download-bundle`
  (манифесты и файлы — `backend/storage/app/public/{releases,bundles}`).

## Секреты

- `/opt/projects/ledgercraft/.env` — `DB_*` для контейнера БД: права `600`, в git не попадает.
- `/opt/projects/ledgercraft/backend/.env` — Laravel (`APP_KEY`, `APP_URL`, `DB_*`,
  `FEEDBACK_PULL_TOKEN`): права `640`, владелец `euegene:www-data`.
  ⚠️ **Права 600 здесь ломают приложение**: php-fpm в контейнере работает под `www-data` (uid 33)
  и файл прочитать не может — Laravel отвечает 500 «No application encryption key has been specified».
- Никогда не печатать содержимое `.env`, ключей, паролей и токенов в логах и отчётах.

## Миграции и деплой

```bash
cd /opt/projects/ledgercraft/backend
git pull
docker exec ledgercraft-app php artisan migrate --force
docker exec ledgercraft-app php artisan config:clear
docker exec ledgercraft-app php artisan route:clear
# если менялся _docker/app/Dockerfile — пересобрать образ:
cd /opt/projects/ledgercraft && docker compose up -d --build ledgercraft-app
```

Откат: `git -C backend checkout <предыдущий-коммит>` → `docker compose up -d --build ledgercraft-app`.
Перед изменением схемы — вручную снять дамп (см. «Бэкап»).

## Ассеты админки Laravel (Vite)

```bash
cd /opt/projects/ledgercraft
docker run --rm -v "$PWD/backend":/app -w /app node:20-alpine sh -c "npm ci --no-audit --no-fund && npm run build"
```

Без `backend/public/build` Laravel отдаёт 500 «Vite manifest not found» на `/login`
(`public/build` в `.gitignore` — в git его нет).

## Промо-страница (с машины разработчика)

```bash
npm run landing:publish -- --server ledgercraft-home \
  --remote-root /opt/projects/ledgercraft/backend/public/promo \
  --url https://ledgercraft.dev.medovf2h.beget.tech/promo/ \
  --api https://ledgercraft.dev.medovf2h.beget.tech/api
```

## Android APK и OTA-бандлы (с машины разработчика)

```bash
# дома системного php с pdo_pgsql нет — artisan запускаем в контейнере
RELEASE_SERVER=ledgercraft-home \
RELEASE_REMOTE_DIR=/opt/projects/ledgercraft/backend \
RELEASE_REMOTE_PHP='docker exec ledgercraft-app php' \
RELEASE_API_URL=https://ledgercraft.dev.medovf2h.beget.tech/api \
npm run release:web -- --channel dev --notes "…"      # OTA-бандл (веб-слой)
# для APK — `npm run release:android` с тем же набором переменных
```

## Проверка

```bash
bash /opt/projects/ledgercraft/smoke.sh                                              # на домашнем сервере
ssh dev-vps 'curl -sSI https://ledgercraft.dev.medovf2h.beget.tech/ | head -3'        # снаружи
ssh dev-vps 'curl -sS  https://ledgercraft.dev.medovf2h.beget.tech/api/app-version'
```

## Бэкап и восстановление

```bash
cd /opt/projects/ledgercraft
docker compose exec -T ledgercraft-db pg_dump -U root -d ledger_craft_db -Fc \
  > /opt/backups/pg/ledgercraft-$(date +%F).dump
# проверка дампа/восстановления — на временную базу:
docker compose exec -T ledgercraft-db psql -U root -d postgres -c 'CREATE DATABASE lc_restore_check'
docker compose exec -T ledgercraft-db pg_restore -U root -d lc_restore_check --clean --if-exists < <dump>
```

Автоматических бэкапов на контуре пока нет (этап 9 инфра-репозитория) — дампы снимаются вручную
в `/opt/backups/pg` (диск ОС `/dev/sda5`).

## Нельзя

- публиковать порты на хост (включая PostgreSQL), использовать `privileged` или `network_mode: host`;
- трогать чужие проекты (старый Ledger-Craft с Traefik на `0.0.0.0:443`/`:8080` и Postgres на `0.0.0.0:5433`)
  и любые диски, кроме диска ОС `/dev/sda5`;
- менять `Caddyfile`, не записав проект в `projects/REGISTRY.md` инфра-репозитория;
- снимать права `640`/группу `www-data` с `backend/.env` — приложение перестанет запускаться.
