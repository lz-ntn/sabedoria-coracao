FROM php:8.3-cli

WORKDIR /app

RUN docker-php-ext-install pdo pdo_mysql mbstring

COPY . .

RUN chmod -R 777 /app

EXPOSE 10000

CMD ["sh", "-c", "php database/migrate.php && php -S 0.0.0.0:10000 -t ."]
