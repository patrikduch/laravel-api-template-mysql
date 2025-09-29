FROM php:8.3-cli

# System deps for building PECL extensions and GD
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    build-essential \
    autoconf \
    pkg-config \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    $PHPIZE_DEPS \
 && docker-php-ext-configure zip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
 && docker-php-ext-install pdo_mysql zip gd \
 && pecl install xdebug-3.3.2 \
 && docker-php-ext-enable xdebug \
 && php -m | grep xdebug \
 && php -m | grep gd \
 && rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Env for Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PATH="/var/www/html/vendor/bin:${PATH}"

# Working directory
WORKDIR /var/www/html

# Optional: Create Xdebug configuration
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
