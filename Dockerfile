FROM php:8.2-apache

# Composer をインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 拡張と依存ライブラリをインストール
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    libcurl4-openssl-dev \
    libxml2-dev \
    libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd pdo pdo_mysql curl zip xml mbstring bcmath

# Apacheのmod_rewrite有効化
RUN a2enmod rewrite

# 作業ディレクトリ設定
WORKDIR /var/www/html

# 全ファイルをコピー（vendor除く）
COPY . .

# Composer install（vendor生成）
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader -vvv
