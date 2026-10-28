<?php
    // This is the file which allows to destroy a session
    require_once __DIR__ . '/includes/bootstrap.php';
    Session::start();
    Session::destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
?>