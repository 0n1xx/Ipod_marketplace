<?php
// Defining a class with regular session methods that are going to be used during the project
class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    public static function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    public static function destroy() {
        session_unset();
        session_destroy();
    }
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>