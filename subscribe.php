<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    Session::start();

    // Only ever redirect back to a local page (never an absolute/external
    // URL, to avoid turning this into an open redirect)
    $redirect = $_POST['redirect'] ?? 'index.php';
    if (!preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $redirect)) {
        $redirect = 'index.php';
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
        header('Location: ' . BASE_URL . "/{$redirect}?subscribe_error=1");
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . "/{$redirect}?subscribe_error=1");
        exit();
    }

    $database = new Database();
    $newsletter = new Newsletter($database->connection);
    $subscribed = $newsletter->subscribe($email);

    // A failed welcome email shouldn't block the subscription itself —
    // the address is already stored either way
    if ($subscribed) {
        Mailer::send(
            $email,
            $L['newsletterEmailSubject'],
            EmailTemplates::newsletterWelcome($L)
        );
    }

    header('Location: ' . BASE_URL . "/{$redirect}?" . ($subscribed ? 'subscribed=1' : 'subscribe_error=1'));
    exit();
?>
