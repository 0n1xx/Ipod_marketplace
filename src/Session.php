<?php
// Defining a class with regular session methods that are going to be used during the project
class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Existing sessions are dropped until the account confirms email
        self::logoutIfEmailUnconfirmed();
    }
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }
    public static function destroy() {
        session_unset();
        session_destroy();
    }
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    private static function logoutIfEmailUnconfirmed(): void {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $script = basename($_SERVER['PHP_SELF'] ?? '');
        $skip = [
            'confirm-email.php',
            'login.php',
            'register.php',
            'logout.php',
            'forgot-password.php',
            'reset-password.php',
            'auth-google.php',
            'auth-google-callback.php',
        ];
        if (in_array($script, $skip, true)) {
            return;
        }

        try {
            $database = new Database();
            $user = new User($database->connection);
            $user->ensureEmailConfirmationSchema();
            $row = $user->findById((int) $_SESSION['user_id']);
            if ($row && (int) ($row['email_confirmed'] ?? 0) === 1) {
                return;
            }
        } catch (Throwable $e) {
            return;
        }

        self::destroy();
        $lang = $_GET['lang'] ?? 'en';
        header('Location: ' . BASE_URL . '/login.php?lang=' . urlencode((string) $lang) . '&reauth=1');
        exit;
    }
}
?>