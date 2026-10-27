<?php
// Single entry point every page requires first: brings in Composer's
// autoloader (so Database, Session, Csrf, User, CrudProducts no longer
// need manual require_once calls), loads environment variables from a
// local .env file if one exists, and defines the DB_* constants.
require_once __DIR__ . '/../vendor/autoload.php';

// safeLoad() does nothing if there is no .env file — on hosts like Railway
// the variables are injected directly into the environment already
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

require_once __DIR__ . '/config.php';
