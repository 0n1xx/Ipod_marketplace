<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/lang.php';

    Session::start();

    $pageTitle = $L['contactTitle'];
    $pageDesc  = $L['contactDesc'];

    $success = null;
    $errors = [];

    $subjectLabels = [
        'general'  => $L['subjectGeneral'],
        'support'  => $L['subjectSupport'],
        'sales'    => $L['subjectSales'],
        'feedback' => $L['subjectFeedback'],
        'other'    => $L['subjectOther'],
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Invalid or expired form submission. Please try again.';
        } else {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName  = trim($_POST['last_name'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $phone     = trim($_POST['phone'] ?? '');
            $subject   = trim($_POST['subject'] ?? '');
            $message   = trim($_POST['message'] ?? '');

            if ($firstName === '' || $lastName === '') {
                $errors[] = $L['contactErrorName'];
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $L['contactErrorEmail'];
            }
            if ($phone === '') {
                $errors[] = $L['contactErrorPhone'];
            }
            if (!isset($subjectLabels[$subject])) {
                $errors[] = $L['contactErrorSubject'];
            }
            if (strlen($message) < 10) {
                $errors[] = $L['contactErrorMessage'];
            }

            if (empty($errors)) {
                $payload = [
                    'first_name'    => $firstName,
                    'last_name'     => $lastName,
                    'email'         => $email,
                    'phone'         => $phone,
                    'subject'       => $subject,
                    'subject_label' => $subjectLabels[$subject],
                    'message'       => $message,
                ];

                // Success if admin inbox OR customer confirmation goes through —
                // Resend can reject one recipient while accepting the other.
                $adminSent = Mailer::send(
                    CONTACT_TO_EMAIL,
                    $L['contactEmailAdminSubject'] . ' — ' . $subjectLabels[$subject],
                    EmailTemplates::contactInquiryAdmin($payload),
                    $email
                );

                $customerSent = Mailer::send(
                    $email,
                    $L['contactEmailCustomerSubject'],
                    EmailTemplates::contactInquiryCustomer($payload, $L)
                );

                if ($adminSent || $customerSent) {
                    $success = $L['contactSuccess'];
                    if (!$adminSent) {
                        error_log('Contact form: customer confirmation sent, but admin email to ' . CONTACT_TO_EMAIL . ' failed');
                    }
                } else {
                    $errors[] = $L['contactErrorSend'];
                }
            }
        }
    }

    require __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container page-head">
        <div>
            <p class="eyebrow"><?= $L['navContact'] ?></p>
            <h1 class="display display--lg"><?= $L['getInTouch'] ?></h1>
        </div>
        <p class="lead"><?= $L['getInTouchText'] ?></p>
    </section>

    <section class="container methods" aria-label="Contact methods">
        <article class="method">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 7 9-7"/></svg>
            <h2><?= $L['emailUs'] ?></h2>
            <p><?= $L['emailUsText'] ?></p>
            <p class="mono"><?= $L['emailAddress'] ?></p>
        </article>
        <article class="method">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v2a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h2a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.1 9.5a16 16 0 0 0 6 6l1.1-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
            <h2><?= $L['callUs'] ?></h2>
            <p><?= $L['callUsHours'] ?></p>
            <p class="mono"><?= $L['phoneNumber'] ?></p>
        </article>
        <article class="method">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <h2><?= $L['visitUs'] ?></h2>
            <p><?= $L['visitUsText'] ?></p>
            <p class="mono"><?= $L['location'] ?></p>
        </article>
    </section>

    <section class="white-band">
        <div class="container split">
            <div>
                <p class="eyebrow"><?= $L['sendMessage'] ?></p>
                <h2 class="display display--sm"><?= $L['sendMessage'] ?></h2>
                <p class="body"><?= $L['sendMessageText'] ?></p>
            </div>
            <div>
                <?php if ($success): ?>
                    <div class="alert_success"><p><?= htmlspecialchars($success) ?></p></div>
                    <div class="success-box">
                        <p><?= htmlspecialchars($success) ?></p>
                        <p><?= $L['sendMessageText'] ?></p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert_failure">
                            <?php foreach ($errors as $err): ?>
                                <p><?= htmlspecialchars($err) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form class="form-stack" action="<?= BASE_URL ?>/contact.php?lang=<?= $lang ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <div class="form-grid">
                            <div class="field">
                                <label for="first-name"><?= $L['firstName'] ?></label>
                                <input class="input" type="text" id="first-name" name="first_name" placeholder="John"
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="last-name"><?= $L['lastName'] ?></label>
                                <input class="input" type="text" id="last-name" name="last_name" placeholder="Doe"
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="email"><?= $L['emailLabel'] ?></label>
                                <input class="input" type="email" id="email" name="email" placeholder="john@example.com"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="phone"><?= $L['phoneLabel'] ?></label>
                                <input class="input" type="tel" id="phone" name="phone" placeholder="+1 (514) 123-4567"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                            </div>
                            <div class="field field--full">
                                <label for="subject"><?= $L['subject'] ?></label>
                                <select class="input" id="subject" name="subject" required>
                                    <?php
                                        $selectedSubject = $_POST['subject'] ?? 'general';
                                        foreach ($subjectLabels as $value => $label):
                                    ?>
                                        <option value="<?= htmlspecialchars($value) ?>" <?= $selectedSubject === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field field--full">
                                <label for="message"><?= $L['message'] ?></label>
                                <textarea class="input" id="message" name="message" rows="8"
                                          placeholder="<?= $L['messagePlaceholder'] ?>" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--dark btn--lg"><?= $L['submitButton'] ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="container section">
        <div class="section-head">
            <h2 class="display display--md"><?= $L['faqTitle'] ?></h2>
            <p class="body"><?= $L['faqSubtitle'] ?></p>
        </div>
        <div class="faq">
            <details>
                <summary><?= $L['faq1Question'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </summary>
                <p><?= $L['faq1Answer'] ?></p>
            </details>
            <details>
                <summary><?= $L['faq2Question'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </summary>
                <p><?= $L['faq2Answer'] ?></p>
            </details>
            <details>
                <summary><?= $L['faq3Question'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </summary>
                <p><?= $L['faq3Answer'] ?></p>
            </details>
            <details>
                <summary><?= $L['faq4Question'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </summary>
                <p><?= $L['faq4Answer'] ?></p>
            </details>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
