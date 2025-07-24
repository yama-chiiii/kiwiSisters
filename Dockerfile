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

# ↑ 最後に bcmath を追加！

# Apacheのmod_rewrite有効化
RUN a2enmod rewrite

# 作業ディレクトリ設定
WORKDIR /var/www/html

# Composer の依存ファイルを先にコピーしてキャッシュ活用
COPY composer.json composer.lock ./

# メモリ制限を外してComposer install（Render環境対策）
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader -vvv

# 残りのファイルをコピー
COPY . .

# 最後に再度 composer install（vendor確実に生成）
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader -vvv

