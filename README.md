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
- Stripe shipping address on checkout → shown in account, admin kanban, receipt email
- Receipt email via Resend after successful Stripe payment
- Admin: inventory, create page, edit page, orders monitor, sold toggle, delete modal, Cloudinary uploads
- Responsive UI (burger ≤900px) — styles in `css/style.css`, behavior in `js/custom-js.js`

## Stack

PHP 8.1+ · MySQL (PDO) · Stripe · Cloudinary · Resend · Railway (Apache/Docker)

## Layout

```
/
├── admin/                 # index, create, edit, orders (+ /orders/ redirect)
├── src/                   # Cart, Csrf, Database, Mailer, Session, CatalogSeeder, Models/
├── includes/              # bootstrap, config, lang loader
├── templates/             # header, footer, product-grid, order-timeline/address/items
├── lang/                  # en.php, fr.php
├── css/style.css          # site styles
├── js/custom-js.js        # nav, toasts, gallery, cart fly, skeletons, confirm modal
├── images/                # product + marketing photography
├── sql/                   # schema + migrations
├── scripts/               # CLI catalog seed
├── redesign-html/         # static mockups (reference only; excluded from Docker image)
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
| `APP_URL` (optional) | Absolute site URL for emails (defaults to the Railway live URL) |

4. Import `sql/schema.sql`. Upgrading an older DB? Also run `sql/migrate_user_cart.sql`, `sql/migrate_order_status.sql`, and `sql/migrate_order_shipping.sql` (shipping columns also auto-apply on first Order use).  
5. Optional catalog pack: `php scripts/seed_extra_products.php` or visit `/seed-catalog.php` as admin  
6. Serve the project root (or deploy the Dockerfile to Railway)

On a sparse/empty DB, `shop.php` runs `CatalogSeeder::ensureIfSparse()` once so the demo catalog appears after deploy without hitting the seeder on every request.

## Admin

- `/admin/` — inventory, mark sold/available, delete (modal confirm)  
- `/admin/orders.php` — kanban: Paid → Shipped → Delivered (+ shipping address)  
- `/admin/create.php` — new product  
- `/admin/edit.php?id=` — edit product + gallery  
- `/sold.php` — sold archive (admin only)  
- `/seed-catalog.php` — JSON seed + sold resync (admin only)

## Notes

- Language: `?lang=en|fr` overrides the cookie and updates it  
- Assets are cache-busted via `ASSET_VERSION` in `includes/config.php`  
- Do not commit secrets (`.env`)

## Author

**Vlad Sakharov**
