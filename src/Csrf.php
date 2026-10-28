<?php
// CSRF tokens are stored in the session and embedded as hidden fields on
// every mutating form. Verification uses hash_equals() so a crafted token
// can't be compared with early-exit timing attacks.
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
