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

                // Admin inbox gets the inquiry (Reply-To = customer).
                // Customer gets a confirmation copy. Resend's free/test
                // domain can reject one recipient while accepting the other,
                // so success = at least one of the two actually sent.
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
<main>
    <section class="intro-contact-page">
        <h1><?php echo $L['getInTouch']; ?></h1>
        <p><?php echo $L['getInTouchText']; ?></p>
    </section>

    <section class="methods-of-contact">
        <div class="contact-method">
            <img src="<?= BASE_URL ?>/images/envelope_contact_page.png" class="image-contact-method" alt="email">
            <h3><?php echo $L['emailUs']; ?></h3>
            <p><?php echo $L['emailUsText']; ?></p>
            <p><?php echo $L['emailAddress']; ?></p>
        </div>
        <div class="contact-method">
            <img src="<?= BASE_URL ?>/images/phone_contact_page.png" class="image-contact-method" alt="phone">
            <h3><?php echo $L['callUs']; ?></h3>
            <p><?php echo $L['callUsHours']; ?></p>
            <p><?php echo $L['phoneNumber']; ?></p>
        </div>
        <div class="contact-method">
            <img src="<?= BASE_URL ?>/images/geolocation_contact_page.png" class="image-contact-method" alt="location">
            <h3><?php echo $L['visitUs']; ?></h3>
            <p><?php echo $L['visitUsText']; ?></p>
            <p><?php echo $L['location']; ?></p>
        </div>
    </section>

    <section class="form-section">
        <div class="form-instructions">
            <h3><?php echo $L['sendMessage']; ?></h3>
            <p><?php echo $L['sendMessageText']; ?></p>
        </div>

        <?php if ($success): ?>
            <div class="alert_success"><p><?= htmlspecialchars($success) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert_failure">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-details">
            <form action="<?= BASE_URL ?>/contact.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <fieldset>
                    <label for="first-name"><?php echo $L['firstName']; ?></label>
                    <input type="text" id="first-name" name="first_name" placeholder="John"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    <label for="last-name"><?php echo $L['lastName']; ?></label>
                    <input type="text" id="last-name" name="last_name" placeholder="Doe"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    <label for="email"><?php echo $L['emailLabel']; ?></label>
                    <input type="email" id="email" name="email" placeholder="john@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <label for="phone"><?php echo $L['phoneLabel']; ?></label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 (514) 123-4567"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    <label for="subject"><?php echo $L['subject']; ?></label>
                    <select id="subject" name="subject" required>
                        <?php
                            $selectedSubject = $_POST['subject'] ?? 'general';
                            foreach ($subjectLabels as $value => $label):
                        ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $selectedSubject === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="message"><?php echo $L['message']; ?></label>
                    <textarea id="message" name="message" rows="10"
                              placeholder="<?php echo $L['messagePlaceholder']; ?>" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </fieldset>
                <div class="submit-button">
                    <button type="submit"><?php echo $L['submitButton']; ?></button>
                </div>
            </form>
        </div>
    </section>

    <section class="frequently-asked-questions">
        <div class="frequently-asked-questions-intro">
            <h2><?php echo $L['faqTitle']; ?></h2>
        </div>
        <details class="question-answer">
            <summary><?php echo $L['faq1Question']; ?></summary>
            <p><?php echo $L['faq1Answer']; ?></p>
        </details>
        <details class="question-answer">
            <summary><?php echo $L['faq2Question']; ?></summary>
            <p><?php echo $L['faq2Answer']; ?></p>
        </details>
        <details class="question-answer">
            <summary><?php echo $L['faq3Question']; ?></summary>
            <p><?php echo $L['faq3Answer']; ?></p>
        </details>
        <details class="question-answer">
            <summary><?php echo $L['faq4Question']; ?></summary>
            <p><?php echo $L['faq4Answer']; ?></p>
        </details>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
