FROM php:8.3-apache

# Extensions MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Outils nécessaires pour compiler l'extension MongoDB
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Apache rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

EXPOSE 80