#!/bin/bash
set -e

APP_PORT="${PORT:-80}"

# Rewrite ports.conf directly instead of using sed
echo "Listen ${APP_PORT}" > /etc/apache2/ports.conf

# Fix VirtualHost port
sed -i "s/:80>/:${APP_PORT}>/g" /etc/apache2/sites-enabled/000-default.conf

# PHP settings
echo "variables_order = EGPCS" >> /etc/php/8.1/apache2/php.ini

# Env vars for Apache/PHP
echo "SetEnv MYSQLHOST ${MYSQLHOST}" >> /etc/apache2/apache2.conf
echo "SetEnv MYSQLPORT ${MYSQLPORT}" >> /etc/apache2/apache2.conf
echo "SetEnv MYSQLDATABASE ${MYSQLDATABASE}" >> /etc/apache2/apache2.conf
echo "SetEnv MYSQLUSER ${MYSQLUSER}" >> /etc/apache2/apache2.conf
echo "SetEnv MYSQLPASSWORD ${MYSQLPASSWORD}" >> /etc/apache2/apache2.conf
echo "SetEnv CLOUDINARY_CLOUD_NAME ${CLOUDINARY_CLOUD_NAME}" >> /etc/apache2/apache2.conf
echo "SetEnv CLOUDINARY_API_KEY ${CLOUDINARY_API_KEY}" >> /etc/apache2/apache2.conf
echo "SetEnv CLOUDINARY_API_SECRET ${CLOUDINARY_API_SECRET}" >> /etc/apache2/apache2.conf

exec apache2ctl -D FOREGROUND