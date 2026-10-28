# QuébecIpod Marketplace

E-commerce site for restored classic iPods — bilingual (EN/FR), Stripe Checkout, Cloudinary media, Resend mail.

**Live:** [https://ipod-marketplace.up.railway.app](https://ipod-marketplace.up.railway.app)

## Features

- Home with restoration story, colour shop-by tiles, Montréal band, FAQ teaser
- Shop (available) + admin-only Sold archive
- Product pages with gallery thumbnails
- Session cart for guests; `user_cart` for logged-in users (survives Railway redeploys)
- Stripe Checkout (CAD) with mobile sticky checkout bar
- Auth: login / register / forgot / reset (split layouts, password strength)
- Contact + newsletter via Resend
- Order statuses: paid → shipped → delivered (account + admin `/admin/orders.php`)
- Receipt email via Resend after successful Stripe payment
- Admin: inventory, create page, edit page, orders monitor, sold toggle, delete modal, Cloudinary uploads
- Responsive UI (burger ≤900px) — styles in `css/style.css`, behavior in `js/custom-js.js`

## Stack

PHP 8.1+ · MySQL (PDO) · Stripe · Cloudinary · Resend · Railway (Apache/Docker)

## Layout

```
/
├── admin/                 # Dashboard, create.php, edit.php
├── src/                   # Cart, Csrf, Database, Mailer, Session, CatalogSeeder, Models/
├── includes/              # bootstrap, config, lang loader
├── templates/             # header, footer, newsletter, product-grid
├── lang/                  # en.php, fr.php
├── css/style.css          # site styles (from redesign-html)
├── js/custom-js.js        # nav, toasts, gallery, password UI, confirm modal
├── images/                # product + marketing photography
├── sql/                   # schema + migrations
├── scripts/               # CLI catalog seed
├── redesign-html/         # static mockups (reference only)
└── *.php                  # public pages at web root
```

HTML emails keep inline CSS (`Mailer` / `EmailTemplates`) because clients strip `<style>` blocks.

## Setup

1. PHP 8.1+, Composer, MySQL  
2. `composer install`  
3. Copy env into `.env` (or Railway variables):

| Variable | Purpose |
|---|---|
| `MYSQLHOST` / `MYSQLPORT` / `MYSQLDATABASE` / `MYSQLUSER` / `MYSQLPASSWORD` | Database |
| `STRIPE_SECRET_KEY` / `STRIPE_PUBLISHABLE_KEY` | Checkout |
| `CLOUDINARY_CLOUD_NAME` / `CLOUDINARY_API_KEY` / `CLOUDINARY_API_SECRET` | Product images |
| `RESEND_API_KEY` / `RESEND_FROM_EMAIL` / `CONTACT_TO_EMAIL` | Mail |

4. Import `sql/schema.sql` (and `sql/migrate_user_cart.sql` if upgrading an older DB)  
5. Optional catalog pack: `php scripts/seed_extra_products.php` or visit `/seed-catalog.php` as admin  
6. Serve the project root (or deploy the Dockerfile to Railway)

On a sparse/empty DB, `shop.php` runs `CatalogSeeder::ensureIfSparse()` once so the demo catalog appears after deploy without hitting the seeder on every request.

## Admin

- `/admin/` — inventory, mark sold/available, delete (modal confirm)  
- `/admin/create.php` — new product  
- `/admin/edit.php?id=` — edit product + gallery  
- `/sold.php` — sold archive (admin only)  
- `/seed-catalog.php` — JSON seed + sold resync (admin only)

## Notes

- Language: `?lang=en|fr` overrides the cookie and updates it  
- Assets are cache-busted via `?v=` on `style.css` / `custom-js.js` in `templates/header.php`  
- Do not commit secrets (`.env`)

## Author

**Vlad Sakharov**
