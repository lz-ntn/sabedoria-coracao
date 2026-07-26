FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip ca-certificates && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 755 /app && chmod +x docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/app/docker-entrypoint.sh"]
