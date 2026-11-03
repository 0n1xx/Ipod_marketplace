<?php
require_once __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/lang.php';

$pageTitle = $lang === 'fr' ? 'Confidentialité' : 'Privacy Policy';
$pageDesc  = $lang === 'fr'
    ? 'Politique de confidentialité QuébecIpod.'
    : 'QuébecIpod privacy policy.';

require __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container about-head">
        <p class="eyebrow"><?= $lang === 'fr' ? 'Légal' : 'Legal' ?></p>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p class="sub"><?= $lang === 'fr' ? 'Dernière mise à jour : 2 novembre 2026' : 'Last updated: November 2, 2026' ?></p>
    </section>

    <section class="container" style="max-width:720px;margin:0 auto 4rem;line-height:1.7;">
        <?php if ($lang === 'fr'): ?>
            <h2>Qui nous sommes</h2>
            <p><strong>QuébecIpod</strong> est une boutique en ligne de démonstration / projet portfolio qui vend des iPod restaurés fictifs ou de démonstration. Site : <a href="https://quebecipod.site">quebecipod.site</a>.</p>

            <h2>Données que nous collectons</h2>
            <p>Nous collectons le nom, le courriel et l’adresse de livraison lorsque vous créez un compte ou passez commande. Les paiements sont traités par Stripe ; nous ne stockons pas les numéros de carte.</p>

            <h2>Connexion Google</h2>
            <p>Si vous vous connectez avec Google, nous recevons uniquement votre courriel et votre nom d’affichage pour créer ou reconnaître votre compte boutique. Ces données ne sont pas utilisées pour la publicité, la revente, ni l’entraînement de modèles d’IA.</p>
            <p>QuébecIpod n’utilise pas les API Google pour générer ou distribuer des images intimes non consenties (AI NCII).</p>

            <h2>Courriels</h2>
            <p>Nous envoyons des courriels transactionnels (confirmation de compte, réinitialisation de mot de passe, reçus) via Resend.</p>

            <h2>Contact</h2>
            <p><a href="mailto:vladsahar27@gmail.com">vladsahar27@gmail.com</a></p>
        <?php else: ?>
            <h2>Who we are</h2>
            <p><strong>QuébecIpod</strong> is a demo / portfolio e-commerce shop for restored classic iPods. Site: <a href="https://quebecipod.site">quebecipod.site</a>.</p>

            <h2>Information we collect</h2>
            <p>We collect your name, email, and shipping address when you create an account or place an order. Payments are processed by Stripe; we do not store card numbers on our servers.</p>

            <h2>Google Sign-In</h2>
            <p>If you sign in with Google, we only receive your email and display name to create or recognize your shop account. We do not use Google user data for advertising, resale, or AI model training.</p>
            <p>QuébecIpod does not use Google APIs for AI-generated non-consensual intimate imagery (AI NCII).</p>

            <h2>Email</h2>
            <p>We send transactional email (account confirmation, password reset, receipts) through Resend.</p>

            <h2>Contact</h2>
            <p><a href="mailto:vladsahar27@gmail.com">vladsahar27@gmail.com</a></p>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
