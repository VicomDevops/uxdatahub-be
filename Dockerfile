FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git zip unzip curl ffmpeg \
    libxml2-dev libzip-dev libpq-dev \
    libjpeg-dev libpng-dev libfreetype6-dev libwebp-dev libxpm-dev

RUN docker-php-ext-install pdo pdo_pgsql pgsql zip intl
RUN docker-php-ext-configure gd --with-jpeg --with-freetype && docker-php-ext-install gd

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/uxdatahub
COPY . .

RUN composer install

CMD ["php-fpm"]