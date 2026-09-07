#!/bin/sh
set -e

echo "[start] booting MENRO ($(date -u))"

echo "[start] running migrations..."
php artisan migrate --force

php artisan storage:link --force 2>/dev/null || true
mkdir -p storage/app/livewire-tmp storage/app/public/avatars
chmod -R 775 storage bootstrap/cache

# Seed only on a fresh database — check the roles table directly, no tinker/PsySH
echo "[start] checking whether database needs seeding..."
NEEDS_SEED=$(php -r '
  $app = require "bootstrap/app.php";
  $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  try {
      echo \Illuminate\Support\Facades\DB::table("roles")->count() > 0 ? "no" : "yes";
  } catch (\Throwable $e) {
      echo "yes";
  }
' 2>/dev/null || echo "yes")

if [ "$NEEDS_SEED" = "yes" ]; then
    echo "[start] seeding database..."
    php artisan db:seed --force
else
    echo "[start] database already seeded — skipping."
fi

echo "[start] caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] starting web server on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
