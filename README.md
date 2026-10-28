# QuébecIpod Marketplace

E-commerce site for restored classic iPods — PHP, MySQL, Stripe Checkout, Cloudinary, Resend, EN/FR.

**Live:** [https://ipod-marketplace.up.railway.app](https://ipod-marketplace.up.railway.app)

## Features

- Shop (available) + admin-only Sold archive
- Product pages with gallery thumbnails
- Session cart for guests; `user_cart` table for logged-in users (survives Railway redeploys)
- Stripe Checkout (CAD)
- Contact + newsletter via Resend
- Admin CRUD, sold toggle, Cloudinary uploads
- Responsive header with burger menu (≤1100px)

## Stack

PHP 8.1+ · MySQL (PDO) · Stripe · Cloudinary · Resend · Railway

## Layout

```
/
├── admin/              # Admin dashboard (auth + is_admin)
├── src/                # Cart, Csrf, Database, Mailer, Session, Models/
├── includes/           # bootstrap, config, lang loader
├── templates/          # header / footer / newsletter
├── lang/               # en.php, fr.php
├── css/style.css       # all site styles (no inline CSS in pages)
├── js/custom-js.js     # nav, toasts, gallery, password rules, confirms
├── sql/                # schema + migrations
└── *.php               # public pages at web root
```

Styles and behavior live in `css/` and `js/` only. HTML emails in `Mailer` / `EmailTemplates` keep inline CSS because clients strip `<style>` blocks.

## Setup

1. PHP 8.1+, Composer, MySQL
2. `composer install`
3. Copy env vars (MySQL, `STRIPE_SECRET_KEY`, Cloudinary, Resend, `CONTACT_TO_EMAIL`) into `.env`
4. Import `sql/schema.sql` (and `sql/migrate_user_cart.sql` if upgrading an older DB)
5. Optional catalog pack: `php scripts/seed_extra_products.php` or `sql/seed_extra_ipods.sql` (12 extra iPods + images under `/images/`)
6. Serve the project root

## Author

**Vlad Sakharov**
