#!/bin/sh
set -e

echo "[start] booting MENRO ($(date -u))"

echo "[start] running migrations..."
php artisan migrate --force

php artisan storage:link --force 2>/dev/null || true
mkdir -p storage/app/livewire-tmp storage/app/public/avatars
chmod -R 775 storage bootstrap/cache

# Seed core data every boot. All five seeders are idempotent (guarded row
# counts / insertOrIgnore / per-username checks), so re-running them on an
# already-populated database is a cheap no-op — and this reliably recovers a
# fresh or partially-seeded database without a fragile "is it seeded?" probe.
# Demo/sample seeders (NotificationSeeder, DemoDataSeeder) are left out of the
# automatic boot — run `php artisan db:seed` by hand if you want sample data.
echo "[start] seeding core data (roles, barangays, lookups, users)..."
php artisan db:seed --force --class=RoleSeeder
php artisan db:seed --force --class=BarangaySeeder
php artisan db:seed --force --class=GeneratorTypeSeeder
php artisan db:seed --force --class=WasteCategorySeeder
php artisan db:seed --force --class=UserSeeder

echo "[start] caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] starting web server on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
