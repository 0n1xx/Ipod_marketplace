FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysql \
    php8.1-gd \
    libapache2-mod-php8.1 \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite php8.1

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN rm -f /var/www/html/index.html

# Важно: меняем порт на $PORT
CMD bash -c "sed -i \"s/Listen 80/Listen \${PORT}/g\" /etc/apache2/ports.conf && \
    sed -i \"s/:80>/:${PORT}>/g\" /etc/apache2/sites-enabled/000-default.conf && \
    apache2ctl -D FOREGROUND"
