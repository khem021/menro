#!/bin/sh
set -e

php artisan migrate --force
php artisan storage:link --force 2>/dev/null || true
mkdir -p storage/app/livewire-tmp storage/app/public/avatars
chmod -R 775 storage bootstrap/cache

# Seed only on a fresh database (no roles = first boot)
ROLE_COUNT=$(php artisan tinker --no-interaction --execute="echo \App\Models\Role::count();" 2>/dev/null | tail -1)
if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
