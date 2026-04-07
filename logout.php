<?php
    // This is the file which allows to destroy a session
    require_once __DIR__ . '/includes/Session.php';
    Session::start();
    Session::destroy();
    header("Location: login.php");
    exit;
?>