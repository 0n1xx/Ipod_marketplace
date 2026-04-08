# iPod Marketplace

A full-stack e-commerce web application dedicated to vintage iPods. This project demonstrates end-to-end development using PHP and MySQL, including authentication, admin CRUD functionality, image uploads, and multilingual support.

**Live Demo:** [https://ipod-marketplace.up.railway.app](https://ipod-marketplace.up.railway.app)

## Overview

This project simulates a real-world online store where users can browse products and view details, while administrators manage inventory through a secure dashboard.

Built using vanilla **HTML**, **CSS**, **JavaScript**, and **PHP**, the application focuses on clean architecture, responsive design, and secure backend practices.

## Features

### User

* Responsive product catalog with dynamic data
* Individual product pages with detailed descriptions
* Search functionality by keywords and product names
* User authentication (register/login/logout)
* Multilingual support (English / French)

### Admin

* Full CRUD operations for products
* Image upload with validation
* Secure session-based authentication
* Admin dashboard for managing inventory

### UI

* Fully responsive layout (Flexbox & Grid)
* Clean and modern interface
* Optimized for desktop, tablet, and mobile


## Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP (Vanilla)
* **Database:** MySQL (PDO, prepared statements)
* **Deployment:** Railway
* **Media Storage:** Cloudinary


## Project Structure

```
/project
│
├── /uploads
├── /css
├── /templates
├── /images
├── /js
├── /lang
├── /includes
├── /admin
│
├── index.php
├── shop.php
├── product.php
├── login.php
├── register.php
└── ...
```

## Database

### products

* id (PK)
* name
* model
* price
* description
* image
* stock
* created_at

### users

* id (PK)
* username
* email
* password (hashed)
* role (user/admin)
* created_at

All database interactions use **PDO prepared statements** to prevent SQL injection.

## Local Setup

1. Install XAMPP / MAMP / Laragon
2. Create MySQL database
3. Import schema
4. Configure DB credentials in `/includes/config.php`
5. Run project via `localhost`

## Ongoing Improvements

* Improving navigation bar responsiveness
* Fixing full name vs username inconsistencies
* Enhancing product description layout
* Refining admin panel UI/UX

## Author

**Vlad Sakharov**
Aspiring Full-Stack Developer

If you found this project interesting, feel free to give it a star.
