FROM php:8.3-cli

WORKDIR /app

RUN docker-php-ext-install pdo pdo_mysql

COPY . .

RUN chmod -R 777 /app

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
