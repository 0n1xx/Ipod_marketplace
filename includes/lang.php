<?php
// First try to get the language from the cookies, so it remembers across all pages
if (isset($_COOKIE['user_lang']) && in_array($_COOKIE['user_lang'], ['en', 'fr'])) {
    $lang = $_COOKIE['user_lang'];
}
// If a brand new user does not have cookies yet
elseif (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr'])) {
    $lang = $_GET['lang'];
    // Remembers for the whole year
    setcookie('user_lang', $lang, time() + (365 * 24 * 60 * 60), "/");
}
// As a default language, I chose english
else {
    $lang = 'en';
}
// Load the correct translation file
$L = require __DIR__ . '/../lang/' . $lang . '.php';
?>