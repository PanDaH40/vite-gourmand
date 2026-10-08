FROM php:8.3-apache

# Extensions MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Outils nécessaires pour MongoDB + Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libssl-dev \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80