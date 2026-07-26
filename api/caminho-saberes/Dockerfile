FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip ca-certificates && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 755 /app && mkdir -p /app/logs

EXPOSE 10000

CMD ["sh", "-c", "php database/migrate.php 2>&1 && echo '=== MIGRATION OK ===' && exec php -S 0.0.0.0:${PORT:-10000} -t . 2>&1"]
