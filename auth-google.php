<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/lang.php';

Session::start();

if (Session::isLoggedIn()) {
    header('Location: ' . BASE_URL . (Session::get('is_admin') == 1 ? '/admin/' : '/personal-account.php'));
    exit();
}

if (!GoogleAuth::isConfigured()) {
    header('Location: ' . BASE_URL . '/login.php?lang=' . urlencode($lang) . '&google_error=1');
    exit();
}

header('Location: ' . GoogleAuth::startUrl());
exit();
