<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/lang.php';

Session::start();

$fail = function () use ($lang) {
    header('Location: ' . BASE_URL . '/login.php?lang=' . urlencode($lang) . '&google_error=1');
    exit();
};

if (!GoogleAuth::isConfigured()) {
    $fail();
}

if (!empty($_GET['error'])) {
    $fail();
}

$code  = $_GET['code']  ?? null;
$state = $_GET['state'] ?? null;

if (!is_string($code) || $code === '' || !GoogleAuth::validateState(is_string($state) ? $state : null)) {
    $fail();
}

$profile = GoogleAuth::fetchUser($code);
if (!$profile) {
    $fail();
}

// Require a verified Google email when the claim is present
if (isset($profile['email_verified']) && $profile['email_verified'] === false) {
    $fail();
}

$database = new Database();
$userModel = new User($database->connection);
$loggedIn = $userModel->findOrCreateFromGoogle(
    (string) $profile['sub'],
    (string) $profile['email'],
    (string) ($profile['name'] ?? '')
);

if (!$loggedIn) {
    $fail();
}

Session::set('user_id',  $loggedIn['id']);
Session::set('username', $loggedIn['name']);
Session::set('is_admin', $loggedIn['is_admin']);
Cart::hydrateFromDbOnLogin((int) $loggedIn['id']);

header('Location: ' . BASE_URL . ($loggedIn['is_admin'] == 1 ? '/admin/' : '/personal-account.php'));
exit();
