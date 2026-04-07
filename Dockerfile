FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli

RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork rewrite

COPY . /var/www/html/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
