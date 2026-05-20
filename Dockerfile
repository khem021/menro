FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        gd \
        intl \
        bcmath \
    && docker-php-ext-enable opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci --ignore-scripts \
    && npm run build \
    && rm -rf node_modules \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-php.ini /usr/local/etc/php/conf.d/uploads.ini
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 10000

CMD ["/bin/sh", "/start.sh"]
