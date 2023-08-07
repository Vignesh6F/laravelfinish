FROM php:7.3-fpm-alpine

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql 

RUN docker-php-ext-install bcmath

