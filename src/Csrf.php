<?php
class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): bool {
        return !empty($_SESSION['csrf_token']) && !empty($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
