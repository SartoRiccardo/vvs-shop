# SmallPlushies

An e-commerce shop built on [Bagisto](https://bagisto.com).

## Local Development

Requirements: PHP 8.3+, Composer, Node.js, and Docker (for MySQL/Redis).

```bash
composer install

# Start MySQL + Redis (dev containers, host ports 3308/6380)
docker compose -f docker-compose.dev.yml up -d

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan db:seed   # seeds locales, channels, currencies, etc. — manual step, not automatic

php artisan serve
```

Frontend assets (Admin/Shop each have their own Vite build):

```bash
cd packages/Webkul/Admin && npm install && npm run dev
cd packages/Webkul/Shop && npm install && npm run dev
```

See `CLAUDE.md` for testing, code style, and translation commands.

## Production Deployment

The production image is built from `docker/production/Dockerfile` (Nginx + PHP-FPM 8.3, supervised).

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Configuration:
- `app` service reads its Laravel env from `.env.api` (not committed — copy from `.env.example` and fill in production values).
  - **`APP_KEY` must be a real, static `base64:...` value** (generate one with `php artisan key:generate --show`). Leaving it blank breaks the app: `env_file` sets `APP_KEY` as an actual container environment variable, which takes precedence over whatever the entrypoint's `key:generate` writes into its internal `.env` — so a blank `APP_KEY` here always wins and Laravel throws `MissingAppKeyException`, no matter what the entrypoint does.
- `mysql` service reads its credentials from `.env.mysql` (not committed — `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`), which must match the `DB_*` values in `.env.api`.
- All services join the external `smallplushies` Docker network.
- The entrypoint (`docker/production/entrypoint.sh`) waits for the database, runs migrations, caches config/routes/views, and re-`chown`s `storage`/`bootstrap/cache` back to `www-data` on every start (migrate/optimize run as root and would otherwise leave files php-fpm can't write to). **It does not seed the database.**

On first deploy against a fresh database, seed manually once:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan db:seed
```

If you're re-testing against data seeded after the app already served a broken response, also clear the full-page response cache (separate from `cache:clear` — `spatie/laravel-responsecache` uses its own `file`-store cache and won't be flushed by the default cache clear):

```bash
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker compose -f docker-compose.prod.yml exec app php artisan responsecache:clear
```

To test the prod image locally without rebuilding, layer `docker-compose.local.yml` (points `app` at an already-built `bagisto:local` image instead of building):

```bash
docker build -f docker/production/Dockerfile -t bagisto:local .
docker compose -f docker-compose.prod.yml -f docker-compose.local.yml up -d
```

## Domain Cutover (staging → production domain)

The plan is to build out the site under a staging domain (`smallplushies.sarto.dev`) and later promote it to the real production domain (`www.smallplushies.com`). This is a single-instance promotion, not a dual-domain deployment — Bagisto's asset/image URLs are derived from a single static `APP_URL`, not per-request `Host` header, so one running instance can only serve one domain correctly at a time. No database migration is needed (image paths are stored relative; only the URL prefix is computed at read time), but the following must be updated at cutover:

1. **`APP_URL`** in `.env.api` → `https://www.smallplushies.com`. This drives asset/image URLs (`config/filesystems.php`, `'url' => env('APP_URL').'/storage'`) and all `url()`/`asset()` output.
2. **Channel `hostname`** (Admin → Settings → Channels) → update to `www.smallplushies.com`. This is what `Core::getCurrentChannel()` matches against the incoming `Host` header for theme/locale/currency resolution.
3. **Re-cache config**: either restart/recreate the `app` container (the entrypoint runs `php artisan optimize` on every start), or from the admin UI use **Configuration → Cache Management → Cache Actions** (runs the same `optimize:clear`/`config:cache` commands without needing container exec access). `APP_URL` is baked into the config cache — a stale cache will keep serving old URLs.
4. **Clear response cache**: `php artisan responsecache:clear` — **not** covered by the Cache Management UI action above (that only touches Laravel's config/route/view caches). The response cache (`spatie/laravel-responsecache`, wired up via Bagisto's FPC package) normally self-invalidates on channel/theme/category changes via event listeners, but a domain change isn't one of those triggers, so cached pages from the old domain need dropping manually via CLI.

## Credits

Built on top of [Bagisto](https://github.com/bagisto/bagisto), an open-source Laravel e-commerce platform, licensed under the [MIT License](https://github.com/bagisto/bagisto/blob/master/LICENSE.txt).
