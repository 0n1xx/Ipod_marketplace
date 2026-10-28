<?php
define('DB_HOST',  $_ENV['MYSQLHOST']     ?? 'localhost');
define('DB_PORT',  $_ENV['MYSQLPORT']     ?? '3306');
define('DB_NAME',  $_ENV['MYSQLDATABASE'] ?? 'railway');
define('DB_USER',  $_ENV['MYSQLUSER']     ?? 'root');
define('DB_PASS',  $_ENV['MYSQLPASSWORD'] ?? '');

define('STRIPE_SECRET_KEY',      $_ENV['STRIPE_SECRET_KEY']      ?? '');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');

define('RESEND_API_KEY',    $_ENV['RESEND_API_KEY']    ?? '');
define('RESEND_FROM_EMAIL', $_ENV['RESEND_FROM_EMAIL'] ?? 'QuébecIpod <onboarding@resend.dev>');