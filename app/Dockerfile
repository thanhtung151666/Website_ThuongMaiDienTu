FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY public/ /var/www/html/
COPY src/ /var/www/src/

RUN chown -R www-data:www-data /var/www/html /var/www/src

EXPOSE 80
