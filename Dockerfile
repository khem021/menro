# syntax=docker/dockerfile:1

############################################
# Stage 1 — Vite / Tailwind assets
############################################
FROM node:20-alpine AS assets

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

# Tailwind's content globs cover resources/** AND app/Http/Livewire/**, so both
# directories have to be here or utility classes used only inside Livewire
# components get purged out of the stylesheet.
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY app ./app

RUN npm run build


############################################
# Stage 2 — runtime
############################################
FROM dunglas/frankenphp:php8.3

# pdo_pgsql  PostgreSQL only: EXTRACT(), pg_get_serial_sequence(), LOWER() LIKE
# gd         dompdf rendering and avatar handling
# zip        phpspreadsheet .xlsx writer
# intl       number/date formatting
# bcmath     dompdf
# opcache    startup time and throughput
# gd and zip must exist BEFORE composer install: dompdf and phpspreadsheet
# declare them as platform requirements and the install aborts without them.
RUN install-php-extensions \
        pdo_pgsql gd zip intl bcmath opcache \
    && apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*

# The FrankenPHP image ships no php.ini at all — without this you silently get
# PHP's compiled-in defaults. Start from the production baseline, then layer
# our overrides on top.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker-php.ini "$PHP_INI_DIR/conf.d/zz-menro.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The image's bundled Caddyfile serves {$SERVER_ROOT:public/} relative to its
# WORKDIR, so the app has to live at /app for the stock config to work.
WORKDIR /app

# Dependencies first, so editing source does not invalidate the composer layer.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader \
        --prefer-dist --no-interaction --no-progress

COPY . .

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && php artisan package:discover --ansi

# public/build is gitignored, so the compiled assets exist only in stage 1.
# Without this COPY every page throws a Vite manifest exception.
COPY --from=assets /build/public/build ./public/build

# .dockerignore strips the .gitignore placeholders that are the only tracked
# content of these directories, so recreate them here. view:cache hard-fails
# without storage/framework/views. The storage/app ones are created again at
# boot because the Railway volume shadows them at runtime.
RUN mkdir -p \
        storage/app/public/avatars \
        storage/app/archives \
        storage/app/livewire-tmp \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache

# Deliberately stays root: Railway mounts volumes as root:root, so dropping to
# www-data makes avatar and report-archive writes fail with EACCES.

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/bin/sh", "/start.sh"]
