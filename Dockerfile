FROM php:8.2-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Установка расширений PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql gd zip

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочего каталога
WORKDIR /var/www

# Копирование приложения в контейнер
COPY . /var/www

# Установка зависимостей через Composer
RUN composer install

# Права на каталоги для кэша и хранения
RUN chown -R www-data:www-data /var/www/storage
RUN chmod -R 775 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
