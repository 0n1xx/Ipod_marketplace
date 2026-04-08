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
RUN echo "PassEnv MYSQLHOST MYSQLPORT MYSQLDATABASE MYSQLUSER MYSQLPASSWORD CLOUDINARY_CLOUD_NAME CLOUDINARY_API_KEY CLOUDINARY_API_SECRET" >> /etc/apache2/apache2.conf

CMD bash -c "\
    sed -i \"s/Listen 80/Listen \${PORT}/g\" /etc/apache2/ports.conf && \
    sed -i \"s/:80>/:\${PORT}>/g\" /etc/apache2/sites-enabled/000-default.conf && \
    echo \"variables_order = EGPCS\" >> /etc/php/8.1/apache2/php.ini && \
    echo \"SetEnv MYSQLHOST \${MYSQLHOST}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv MYSQLPORT \${MYSQLPORT}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv MYSQLDATABASE \${MYSQLDATABASE}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv MYSQLUSER \${MYSQLUSER}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv MYSQLPASSWORD \${MYSQLPASSWORD}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv CLOUDINARY_CLOUD_NAME \${CLOUDINARY_CLOUD_NAME}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv CLOUDINARY_API_KEY \${CLOUDINARY_API_KEY}\" >> /etc/apache2/apache2.conf && \
    echo \"SetEnv CLOUDINARY_API_SECRET \${CLOUDINARY_API_SECRET}\" >> /etc/apache2/apache2.conf && \
    apache2ctl -D FOREGROUND"
