FROM php:8.2-apache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip unzip curl \
    libcurl4-openssl-dev libxml2-dev libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd pdo pdo_mysql curl zip xml mbstring bcmath

RUN a2enmod rewrite

WORKDIR /var/www/html

# 👇 ここで composer 実行前にファイルを先に置く
COPY composer.json composer.lock ./
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader -vvv

COPY . .

# 念押しで再 install（vendor が上書きされた場合に備えて）
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader -vvv
