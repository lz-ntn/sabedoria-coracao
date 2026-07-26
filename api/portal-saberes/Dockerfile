FROM php:8.3-cli

RUN apt-get update && apt-get install -y libonig-dev libpng-dev libjpeg-dev git unzip ca-certificates && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup --gid 1000 app && adduser --uid 1000 --gid 1000 --disabled-password --gecos "" app

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p uploads && chown -R app:app uploads && chmod -R 755 uploads

RUN chown -R app:app /app && chmod -R 755 /app && chmod -R 775 uploads

EXPOSE 10000

USER app

CMD ["sh", "-c", "php database/migrate.php && php -S 0.0.0.0:10000 -t ."]
