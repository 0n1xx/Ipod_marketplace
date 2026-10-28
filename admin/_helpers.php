<?php
// Shared admin helpers (included by index.php / edit.php)

function adminRequireAuth(): void {
    Session::start();
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
    if (Session::get('is_admin') != 1) {
        // Sold archive redirects to shop; other admin tools go to account
        $fallback = str_ends_with($_SERVER['SCRIPT_NAME'] ?? '', '/sold.php')
            ? '/shop.php'
            : '/personal-account.php';
        header('Location: ' . BASE_URL . $fallback);
        exit();
    }
}

// PHP groups multi-file inputs as $_FILES['x']['name'][0..n]; reshape to per-file arrays
function normalizeMultiFiles(array $filesField): array {
    $normalized = [];
    foreach ($filesField['name'] as $i => $name) {
        $normalized[] = [
            'name'     => $name,
            'tmp_name' => $filesField['tmp_name'][$i],
            'error'    => $filesField['error'][$i],
            'size'     => $filesField['size'][$i],
        ];
    }
    return $normalized;
}
