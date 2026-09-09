FROM php:8.3-cli-bookworm
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev unzip \
    && docker-php-ext-install pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize
USER www-data
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/router.php"]
