FROM php:8.3-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    bash git curl zip unzip icu-dev libzip-dev oniguruma-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    autoconf g++ make linux-headers shadow \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql mbstring intl bcmath zip gd opcache pcntl \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del autoconf g++ make linux-headers \
 && rm -rf /var/cache/apk/* /tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Map container www-data to host UID/GID for volume permissions
RUN groupmod -o -g ${GID} www-data \
 && usermod  -o -u ${UID} -g www-data www-data

WORKDIR /var/www/html

USER www-data
