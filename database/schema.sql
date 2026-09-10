CREATE DATABASE IF NOT EXISTS gonzagas_apparel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gonzagas_apparel;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS subscribers;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL DEFAULT '',
    address_line1 VARCHAR(190) NOT NULL DEFAULT '',
    address_line2 VARCHAR(190) NOT NULL DEFAULT '',
    city VARCHAR(100) NOT NULL DEFAULT '',
    province VARCHAR(100) NOT NULL DEFAULT '',
    postal_code VARCHAR(20) NOT NULL DEFAULT '',
    country VARCHAR(100) NOT NULL DEFAULT 'Philippines',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(500) NOT NULL DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(2000) NOT NULL DEFAULT '',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL DEFAULT 'black-tshirt-placeholder.svg',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_products_category (category_id),
    INDEX idx_products_name (name)
) ENGINE=InnoDB;

CREATE TABLE subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    subtotal_amount DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod',
    shipping_name VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(30) NOT NULL,
    shipping_address1 VARCHAR(190) NOT NULL,
    shipping_address2 VARCHAR(190) NOT NULL DEFAULT '',
    shipping_city VARCHAR(100) NOT NULL,
    shipping_province VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(100) NOT NULL DEFAULT 'Philippines',
    notes VARCHAR(500) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_orders_user_created (user_id, created_at),
    INDEX idx_orders_status_created (status, created_at)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(150) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name,email,password_hash,role,status) VALUES
('Gonzaga Admin','admin@gonzagas.local','$2y$12$01y7sVTKeYO36ie5lZjJzOIUBrWczUB.E.q2RFJ7BhfLfFLvd/Uta','admin','active');

INSERT INTO profiles (user_id) SELECT id FROM users WHERE email='admin@gonzagas.local';

INSERT INTO categories (name,slug,description) VALUES
('T-Shirts','t-shirts','Essential everyday tops.'),
('Hoodies','hoodies','Comfort-first layers.'),
('Accessories','accessories','Caps, bags, and everyday carry pieces.');

INSERT INTO products (category_id,name,description,price,stock,image,is_active) VALUES
((SELECT id FROM categories WHERE slug='t-shirts'),'ESSENTIAL BLACK T-SHIRT','Minimal everyday essential.',899.00,25,'black-tshirt-placeholder.svg',1),
((SELECT id FROM categories WHERE slug='t-shirts'),'ESSENTIAL WHITE T-SHIRT','Clean, versatile everyday essential.',899.00,25,'white-tshirt-placeholder.svg',1),
((SELECT id FROM categories WHERE slug='hoodies'),'MINIMAL HOODIE','Comfort-first minimal hoodie.',1599.00,15,'hoodie-placeholder.svg',1),
((SELECT id FROM categories WHERE slug='accessories'),'CLASSIC CAP','Timeless cap for everyday styling.',699.00,20,'cap-placeholder.svg',1),
((SELECT id FROM categories WHERE slug='accessories'),'LOGO TOTE BAG','Simple tote for everyday carry.',499.00,30,'tote-placeholder.svg',1);
