FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip libpng-dev \
    && docker-php-ext-install zip pdo pdo_mysql gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD php -S 0.0.0.0:$PORT -t public