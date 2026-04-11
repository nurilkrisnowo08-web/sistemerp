FROM php:8.3.28-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip \
    nodejs npm \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Install Node deps & build
RUN npm install && npm run build

CMD php -S 0.0.0.0:$PORT -t public