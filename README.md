# iPod Marketplace

Hey! This is my biggest and most complete project so far – a full-featured online marketplace dedicated to vintage iPods. It's a dynamic web application where users can browse products, view details, and admins can fully manage the inventory through a dedicated panel.

Built entirely with vanilla **HTML**, **CSS**, **JavaScript**, and **PHP** (with MySQL for the backend), this project brings together everything I've learned: responsive design, server-side logic, secure database interactions, user authentication, file uploads, and even multilingual support.

### Key Features
- **Product Catalog**: Responsive grid of product cards with images, names, prices, and quick details.
- **Individual Product Pages**: Dynamic pages pulling data from the database to show full descriptions, specs, multiple images if available, and pricing.
- **Search & Filtering**: Basic search functionality to find iPods by name, model, or keywords.
- **User System**: Registration, login/logout with session management for secure access (admins have elevated privileges).
- **Admin Dashboard**: Complete CRUD operations – add new products, edit existing ones, update details/images, and delete items safely.
- **Image Uploads**: Secure handling of product images with validation and storage on the server.
- **Multilingual Support**: English and French versions (language switcher toggles content dynamically).
- **Responsive Design**: Looks great on desktop, tablet, and mobile using Flexbox/Grid and media queries.

This project feels like a real e-commerce site and was a huge step up for me in organizing code, handling security (prepared statements, input validation), and building reusable components.

### Project Structure
```
/project
│
├── /uploads
│
├── /css
│   └── style.css
│
│
│── /templates
│   ├── header.php
│   ├── additional_info.php
│   └── footer.php
│
│── /images
│  
│── /js
│   └── custom-js.js
│
│── /lang
│   ├── en.php
│   ├── fr.php
│
├── /includes
│   ├── Database.php
│   ├── Session.php
│   ├── config.php
│   └── lang.php
│
├── /admin
│   ├── CrudProducts.php
│   └── User.php
│   
│
├── README.MD  
├── about.php
├── admin-dashboard.php
├── home.php
├── login.php
├── logout.php
├── shop.php
├── product.php
├── personal-account.php
├── contact.php
└── register.php
```

### Database Schema
Simple and effective MySQL setup with security in mind.

**Table: `products`**

| Column            | Type             | Description                                      |
|-------------------|------------------|--------------------------------------------------|
| `id`              | INT AUTO_INCREMENT PRIMARY KEY | Unique product ID                          |
| `name`            | VARCHAR(255)     | Product title (e.g., "iPod Classic 5th Gen")     |
| `model`           | VARCHAR(100)     | Model specifications                             |
| `price`           | DECIMAL(10,2)    | Price                                            |
| `description`     | TEXT             | Full product description                         |
| `short_desc`      | TEXT             | Short summary for listings                       |
| `image`           | VARCHAR(255)     | Path to uploaded image                           |
| `stock`           | INT              | Quantity (if tracked)                            |
| `created_at`      | TIMESTAMP        | Date added                                       |

**Table: `users`**

| Column            | Type             | Description                                      |
|-------------------|------------------|--------------------------------------------------|
| `id`              | INT AUTO_INCREMENT PRIMARY KEY | Unique user ID                             |
| `username`        | VARCHAR(50)      | Login name                                       |
| `email`           | VARCHAR(100)     | User email                                       |
| `password`        | VARCHAR(255)     | Hashed password                                  |
| `role`            | ENUM('user','admin') | Access level                                 |
| `created_at`      | TIMESTAMP        | Registration date                                |

All queries use PDO prepared statements and password hashing for security.

### How to Run Locally
1. Set up a local PHP + MySQL environment (XAMPP, Laragon, MAMP, etc.)
2. Create the database and tables (use the schema above or any provided dump)
3. Update credentials in `includes/db.php`
4. Place the `Ipod_marketplace` folder in your web server root
5. Open `http://localhost/Ipod_marketplace/index.php` in your browser

I'm really proud of this project – it started as a simple listing page and evolved into a proper full-stack application. Future ideas include adding a cart, pagination, categories, and maybe user reviews.

Thanks for checking it out! Feedback is always appreciated.

— Vlad Sakharov  
Aspiring Full-Stack Developer
