FROM php:8.2-fpm

# Installing dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Installing PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql gd zip

# Installing Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setting working directory
WORKDIR /var/www

# Copying the application into the container
COPY . /var/www

# Installing dependencies via Composer
RUN composer install

# Setting permissions for cache and storage directories
RUN chown -R www-data:www-data /var/www/storage
RUN chmod -R 775 /var/www/storage

EXPOSE 9000
