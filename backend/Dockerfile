#1 gunakan image dasar php
FROM php:8.4-cli

#2 instal depedensi
RUN apt-get update && apt-get install -y libzip-dev libpng-dev libjpeg-dev zip unzip && docker-php-ext-install pdo pdo_mysql zip gd

#3 install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#4 tetapkan direktori kerja di container
WORKDIR /var/www