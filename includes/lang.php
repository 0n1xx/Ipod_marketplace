<?php
// Explicit ?lang= wins (so the header language toggle always works),
// then remember the choice in a cookie. Fall back to cookie, then English.
$allowedLangs = ['en', 'fr'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs, true)) {
    $lang = $_GET['lang'];
    setcookie('user_lang', $lang, [
        'expires'  => time() + (365 * 24 * 60 * 60),
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
} elseif (isset($_COOKIE['user_lang']) && in_array($_COOKIE['user_lang'], $allowedLangs, true)) {
    $lang = $_COOKIE['user_lang'];
} else {
    $lang = 'en';
}

$L = require __DIR__ . '/../lang/' . $lang . '.php';
