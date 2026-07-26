FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 /app

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
