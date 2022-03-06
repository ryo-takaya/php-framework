FROM php:7.4-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/web

RUN docker-php-ext-install pdo_mysql
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled