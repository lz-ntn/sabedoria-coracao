FROM php:8.3-cli

RUN apt-get update && apt-get install -y libonig-dev libpng-dev libjpeg-dev git unzip ca-certificates && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 755 /app && cp docker-entrypoint.sh /docker-entrypoint.sh && chmod +x /docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/docker-entrypoint.sh"]
