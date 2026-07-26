FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip ca-certificates && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup --gid 1000 app && adduser --uid 1000 --gid 1000 --disabled-password --gecos "" app

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R app:app /app && chmod -R 755 /app

EXPOSE 10000

USER app

CMD ["sh", "-c", "php database/migrate.php 2>&1 && php -S 0.0.0.0:${PORT:-10000} -t . 2>&1"]
