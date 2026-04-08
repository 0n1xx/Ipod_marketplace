#!/bin/bash
set -e

APP_PORT="${PORT:-80}"

# Fix Apache port
echo "Listen ${APP_PORT}" > /etc/apache2/ports.conf
sed -i "s/:80>/:${APP_PORT}>/g" /etc/apache2/sites-enabled/000-default.conf

# Pass ALL env vars to PHP via php.ini (avoids SetEnv Apache issues)
PHP_INI="/etc/php/8.1/apache2/php.ini"
echo "variables_order = EGPCS" >> "$PHP_INI"
echo "env[MYSQLHOST] = \"${MYSQLHOST}\"" >> "$PHP_INI"
echo "env[MYSQLPORT] = \"${MYSQLPORT}\"" >> "$PHP_INI"
echo "env[MYSQLDATABASE] = \"${MYSQLDATABASE}\"" >> "$PHP_INI"
echo "env[MYSQLUSER] = \"${MYSQLUSER}\"" >> "$PHP_INI"
echo "env[MYSQLPASSWORD] = \"${MYSQLPASSWORD}\"" >> "$PHP_INI"
echo "env[CLOUDINARY_CLOUD_NAME] = \"${CLOUDINARY_CLOUD_NAME}\"" >> "$PHP_INI"
echo "env[CLOUDINARY_API_KEY] = \"${CLOUDINARY_API_KEY}\"" >> "$PHP_INI"
echo "env[CLOUDINARY_API_SECRET] = \"${CLOUDINARY_API_SECRET}\"" >> "$PHP_INI"

exec apache2ctl -D FOREGROUND