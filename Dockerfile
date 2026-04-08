FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysql \
    php8.1-gd \
    php8.1-curl \
    libapache2-mod-php8.1 \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /var/www/html/index.html

RUN a2enmod rewrite php8.1
RUN sed -i 's/DirectoryIndex index.html index.cgi index.pl index.php/DirectoryIndex index.php index.html/g' /etc/apache2/mods-enabled/dir.conf
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

CMD ["/docker-entrypoint.sh"]