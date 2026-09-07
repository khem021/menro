#!/bin/sh
set -e

echo "[start] booting MENRO ($(date -u))"

echo "[start] running migrations..."
php artisan migrate --force

php artisan storage:link --force 2>/dev/null || true
mkdir -p storage/app/livewire-tmp storage/app/public/avatars
chmod -R 775 storage bootstrap/cache

# Seed the essentials whenever the admin login user is missing. All five
# seeders below are idempotent (guarded counts / insertOrIgnore), so this is
# safe to run on every boot and recovers from a partially-seeded database.
# Demo/sample seeders (NotificationSeeder, DemoDataSeeder) are intentionally
# left out of the automatic boot — run `php artisan db:seed` manually if wanted.
echo "[start] checking whether core data needs seeding..."
NEEDS_SEED=$(php -r '
  $app = require "bootstrap/app.php";
  $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  try {
      echo \Illuminate\Support\Facades\DB::table("users")->where("username", "admin")->exists() ? "no" : "yes";
  } catch (\Throwable $e) {
      echo "yes";
  }
' 2>/dev/null || echo "yes")

if [ "$NEEDS_SEED" = "yes" ]; then
    echo "[start] seeding core data (roles, barangays, lookups, users)..."
    php artisan db:seed --force --class=RoleSeeder
    php artisan db:seed --force --class=BarangaySeeder
    php artisan db:seed --force --class=GeneratorTypeSeeder
    php artisan db:seed --force --class=WasteCategorySeeder
    php artisan db:seed --force --class=UserSeeder
else
    echo "[start] core data present — skipping seed."
fi

echo "[start] caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] starting web server on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
