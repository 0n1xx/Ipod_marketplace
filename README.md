# QuébecIpod Marketplace

Demo e-commerce shop for restored classic iPods — bilingual EN/FR, built as a portfolio / COMP project.

Browse the catalog, create an account, check out with Stripe (test mode), and manage inventory as an admin.

**Live:** [https://quebecipod.site](https://quebecipod.site)

---

## Features

- Shop + product pages with galleries; admin-only sold archive
- Cart (session for guests, `user_cart` for logged-in users)
- Stripe Checkout (CAD) with shipping address and order receipts
- Order flow: paid → shipped → delivered (account + admin kanban)
- Auth:
  - Email / password registration with **email confirmation** (Resend)
  - Forgot / reset password
  - **Google Sign-In**
- Contact form + newsletter via Resend
- Admin CRUD: products, images (Cloudinary), orders
- Responsive UI (`css/style.css`, `js/custom-js.js`)

---

## Stack

PHP 8.1+ · MySQL (PDO) · Stripe · Cloudinary · Resend · Google OAuth · Railway

---

## Auth notes

- New local accounts must confirm email before sign-in
- Unconfirmed users are signed out until they confirm (or use Google)
- Google Sign-In is only for account authentication (email + display name)
- Privacy policy: [/privacy.php](https://quebecipod.site/privacy.php)

---

## Setup

1. PHP 8.1+, Composer, MySQL  
2. `composer install`  
3. Set env (`.env` or Railway):

| Variable | Purpose |
|---|---|
| `MYSQLHOST` / `MYSQLPORT` / `MYSQLDATABASE` / `MYSQLUSER` / `MYSQLPASSWORD` | Database |
| `STRIPE_SECRET_KEY` / `STRIPE_PUBLISHABLE_KEY` | Checkout |
| `CLOUDINARY_CLOUD_NAME` / `CLOUDINARY_API_KEY` / `CLOUDINARY_API_SECRET` | Product images |
| `RESEND_API_KEY` | Mail API |
| `RESEND_FROM_EMAIL` | e.g. `QuébecIpod <noreply@quebecipod.site>` |
| `CONTACT_TO_EMAIL` | Inbox for contact form |
| `APP_URL` | `https://quebecipod.site` (emails + OAuth redirect) |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Google Sign-In (button hidden if unset) |

Google Cloud OAuth **Web** client:

- Origin: `https://quebecipod.site`
- Redirect: `https://quebecipod.site/auth-google-callback.php`

4. Import `sql/schema.sql`. Upgrading an older DB? Run migrations under `sql/` (`migrate_user_cart`, `migrate_order_status`, `migrate_order_shipping`, `migrate_google_oauth`, `migrate_email_confirmation`). `google_id` / `email_confirmed` also auto-add on first use.  
5. Optional seed: `php scripts/seed_extra_products.php` or `/seed-catalog.php` as admin  
6. Deploy with the Dockerfile on Railway (custom domain `quebecipod.site`)

---

## Admin

- `/admin/` — inventory  
- `/admin/orders.php` — Paid → Shipped → Delivered  
- `/admin/create.php` / `/admin/edit.php?id=` — products + gallery  
- `/sold.php` — sold archive (admin only)

---

## Notes

- Language: `?lang=en|fr`  
- Cache-bust assets via `ASSET_VERSION` in `includes/config.php`  
- Do not commit secrets (`.env`)

---

## Author

Vladislav Sakharov
