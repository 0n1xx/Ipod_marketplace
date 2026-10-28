<?php
// Shared admin helpers (included by index.php / edit.php)

function adminRequireAuth(): void {
    Session::start();
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
    if (Session::get('is_admin') != 1) {
        header('Location: ' . BASE_URL . '/personal-account.php');
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
