#!/bin/sh
set -e

echo "[start] MENRO boot $(date -u)"
cd /app

# ---------------------------------------------------------------------------
# 1. Recreate what the volume shadows.
#    The Railway volume mounts at /app/storage/app at RUNTIME ONLY, hiding
#    whatever the image put there. storage/framework and storage/logs are not
#    on the volume, but their .gitignore placeholders are dockerignored, so
#    make those too rather than trust the image layout.
# ---------------------------------------------------------------------------
mkdir -p \
    storage/app/public/avatars \
    storage/app/archives \
    storage/app/livewire-tmp \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# public/storage lives on the container filesystem, not the volume, so the
# symlink has to be recreated every boot. Its target must already exist (above)
# or /storage/avatars/* serves 404.
php artisan storage:link --force 2>/dev/null || true

# ---------------------------------------------------------------------------
# 2. Schema and data.
# ---------------------------------------------------------------------------
echo "[start] migrating"
php artisan migrate --force

if [ "${SEED_ON_BOOT:-true}" = "true" ]; then
    echo "[start] seeding core data"
    # All six are idempotent: guarded row counts, insertOrIgnore, or per-username
    # checks. UserSeeder never touches an existing user, so a password changed in
    # production survives. FreshDemoSeeder bails out once waste_entries has rows.
    php artisan db:seed --force --class=RoleSeeder
    php artisan db:seed --force --class=BarangaySeeder
    php artisan db:seed --force --class=GeneratorTypeSeeder
    php artisan db:seed --force --class=WasteCategorySeeder
    php artisan db:seed --force --class=UserSeeder
    php artisan db:seed --force --class=FreshDemoSeeder
else
    echo "[start] SEED_ON_BOOT=false — skipping seeders"
fi

# ---------------------------------------------------------------------------
# 3. Caches. These MUST run here and not in the Dockerfile: Railway injects
#    DATABASE_URL / APP_KEY / APP_URL at runtime only, and config:cache freezes
#    whatever env() sees at the moment it runs.
# ---------------------------------------------------------------------------

# APP_URL is normally set to https://${{RAILWAY_PUBLIC_DOMAIN}}, but that
# reference resolves to an empty string until a domain actually exists — which
# would freeze APP_URL as a bare "https://" into the config cache and 404 every
# avatar, because config/filesystems.php builds the public disk URL from it.
# Recover the value from the runtime variable so deploy order stops mattering.
if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    case "${APP_URL:-}" in
        "" | "https://" | "http://")
            export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
            echo "[start] APP_URL was empty, recovered as ${APP_URL}"
            ;;
    esac
fi

if [ -z "${APP_URL:-}" ] || [ "${APP_URL}" = "https://" ]; then
    echo "[start] WARNING: APP_URL is not set and no Railway domain exists yet."
    echo "[start]          Generate a domain, then redeploy, or avatars will 404."
fi

echo "[start] caching config, routes and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---------------------------------------------------------------------------
# 4. Scheduler, in this container on purpose.
#    A Railway volume attaches to exactly one service, so a separate scheduler
#    service could not write report archives where ArchiveController reads them.
#    More importantly CACHE_DRIVER=file is per-container: the Cache::forget()
#    calls in FlagOverdueFollowUps and DecayComplianceStatus would clear a
#    throwaway cache instead of the one the dashboard actually reads.
# ---------------------------------------------------------------------------
if [ "${RUN_SCHEDULER:-true}" = "true" ]; then
    echo "[start] starting scheduler"
    (
        while true; do
            php artisan schedule:work || echo "[scheduler] exited, restarting in 10s"
            sleep 10
        done
    ) &
fi

# ---------------------------------------------------------------------------
# 5. Web server. A bare ":PORT" with no hostname keeps Caddy's automatic HTTPS
#    off, which is required because Railway's edge terminates TLS. exec so
#    FrankenPHP is PID 1 and receives SIGTERM on redeploy.
# ---------------------------------------------------------------------------
export SERVER_NAME=":${PORT:-8080}"
export SERVER_ROOT=/app/public

echo "[start] FrankenPHP listening on ${SERVER_NAME}"
exec frankenphp run --config /etc/caddy/Caddyfile
