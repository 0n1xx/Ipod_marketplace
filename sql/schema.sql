-- Table: admin_users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0
);

-- Table: products_description
CREATE TABLE products_description (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL
);

-- If a user should become an admin, here is a SQL script for that 
update admin_users set is_admin = 1 where name = 'your_user_name';
