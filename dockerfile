FROM node:18 as nodebuilder
WORKDIR /app
COPY package*.json ./
RUN npm install --omit=dev
COPY . .

FROM php:8.2-cli
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install gd

WORKDIR /app
COPY --from=nodebuilder /app /app

RUN composer install

CMD php artisan serve --host=0.0.0.0 --port=8080