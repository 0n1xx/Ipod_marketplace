# iPod Marketplace

A full-stack e-commerce web application dedicated to vintage iPods. This project demonstrates end-to-end development using PHP and MySQL, including authentication, admin CRUD functionality, image uploads, Stripe checkout, and multilingual support.

**Live Demo:** [https://ipod-marketplace.up.railway.app](https://ipod-marketplace.up.railway.app)

## Overview

This project simulates a real-world online store where users can browse products, manage a cart, and check out, while administrators manage inventory through a secure dashboard.

Built using vanilla **HTML**, **CSS**, **JavaScript**, and **PHP**, the application focuses on clean architecture, responsive design, and secure backend practices.

## Features

### User

* Responsive product catalog with dynamic data
* Individual product pages with gallery photos
* Search and sort in the shop
* Cart + Stripe Checkout
* User authentication (register / login / password reset)
* Multilingual support (English / French)

### Admin

* Full CRUD operations for products
* Image upload (Cloudinary) with gallery support
* Sold / available status toggles
* Secure session-based authentication

### UI

* Fully responsive layout (Flexbox & Grid)
* Shared design system (tokens, toasts, focus rings)
* Optimized for desktop, tablet, and mobile

## Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP (Vanilla)
* **Database:** MySQL (PDO, prepared statements)
* **Payments:** Stripe Checkout
* **Deployment:** Railway
* **Media Storage:** Cloudinary

## Project Structure

```
/
├── admin/                 # Admin portal (dashboard UI)
│   └── index.php
├── src/                   # Application code (not web pages)
│   ├── Models/            # Product, Order, User, Newsletter
│   ├── Cart.php
│   ├── Csrf.php
│   ├── Database.php
│   ├── Mailer.php
│   └── Session.php
├── includes/              # Bootstrap, config, i18n loader
├── templates/             # Shared header / footer
├── lang/                  # en.php / fr.php
├── css/  js/  images/
├── uploads/
├── sql/
├── index.php              # Public pages live at the web root
├── shop.php
├── cart.php
├── checkout.php
└── ...
```

`admin-dashboard.php` remains as a 301 redirect to `/admin/` for old links.

## Database

All database interactions use **PDO prepared statements** to prevent SQL injection. See `sql/schema.sql` for the current schema.

## Local Setup

1. Install PHP 8.1+, Composer, and MySQL (or use Docker)
2. `composer install`
3. Copy environment variables (MySQL, Stripe, Cloudinary, Resend) into a `.env`
4. Import `sql/schema.sql`
5. Serve the project document root (Apache / PHP built-in server)

## Author

**Vlad Sakharov**
Aspiring Full-Stack Developer

If you found this project interesting, feel free to give it a star.
