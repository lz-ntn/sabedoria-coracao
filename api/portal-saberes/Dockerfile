FROM php:8.3-cli

RUN apt-get update && apt-get install -y libonig-dev libpng-dev libjpeg-dev git unzip && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 /app

EXPOSE 10000

CMD ["sh", "-c", "php database/migrate.php && php -S 0.0.0.0:10000 -t ."]
