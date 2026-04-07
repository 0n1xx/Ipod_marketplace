<?php
    // Requiring the file with the languages
    require __DIR__ . '/includes/lang.php';
    $pageTitle = $L['contactTitle'];
    $pageDesc  = $L['contactDesc'];
    require "./templates/header.php";
?>
<main>
    <section class="intro-contact-page">
        <h1><?php echo $L['getInTouch']; ?></h1>
        <p><?php echo $L['getInTouchText']; ?></p>
    </section>
    <!-- Variables how the user can contact us -->
    <section class="methods-of-contact">
        <div class="contact-method">
            <img src="./images/envelope_contact_page.png" class="image-contact-method" alt="email">
            <h3><?php echo $L['emailUs']; ?></h3>
            <p><?php echo $L['emailUsText']; ?></p>
            <p><?php echo $L['emailAddress']; ?></p>
        </div>
        <div class="contact-method">
            <img src="./images/phone_contact_page.png" class="image-contact-method" alt="phone">
            <h3><?php echo $L['callUs']; ?></h3>
            <p><?php echo $L['callUsHours']; ?></p>
            <p><?php echo $L['phoneNumber']; ?></p>
        </div>
        <div class="contact-method">
            <img src="./images/geolocation_contact_page.png" class="image-contact-method" alt="location">
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
        <!-- The form below potentially allows us to contact users-->
        <div class="form-details">
            <form action="contact.php" method="post">
                <fieldset>
                    <label for="first-name"><?php echo $L['firstName']; ?></label>
                    <input type="text" id="first-name" name="first_name" placeholder="John" required>
                    <label for="last-name"><?php echo $L['lastName']; ?></label>
                    <input type="text" id="last-name" name="last_name" placeholder="Doe" required>
                    <label for="email"><?php echo $L['emailLabel']; ?></label>
                    <input type="email" id="email" name="email" placeholder="john@example.com" required>
                    <label for="phone"><?php echo $L['phoneLabel']; ?></label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 (514) 123-4567" required>
                    <label for="subject"><?php echo $L['subject']; ?></label>
                    <select id="subject" name="subject" required>
                        <option value="" disabled selected><?php echo $L['subjectGeneral']; ?></option>
                        <option value="support"><?php echo $L['subjectSupport']; ?></option>
                        <option value="sales"><?php echo $L['subjectSales']; ?></option>
                        <option value="feedback"><?php echo $L['subjectFeedback']; ?></option>
                        <option value="other"><?php echo $L['subjectOther']; ?></option>
                    </select>
                    <label for="message"><?php echo $L['message']; ?></label>
                    <textarea id="message" name="message" rows="10" placeholder="<?php echo $L['messagePlaceholder']; ?>" required></textarea>
                </fieldset>
                <div class="submit-button">
                    <button type="submit"><?php echo $L['submitButton']; ?></button>
                </div>
            </form>
        </div>
    </section>
    <!-- Adds a section with frequently asked questions -->
    <section class="frequently-asked-questions">
        <div class="frequently-asked-questions-intro">
            <h2><?php echo $L['faqTitle']; ?></h2>
        </div>
        <!-- Found this html elements which is way easier to use than implement this logic using js -->
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
<!-- Calling the footer -->
<?php require "./templates/footer.php"; ?>