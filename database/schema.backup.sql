CREATE DATABASE IF NOT EXISTS gonzagas_apparel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gonzagas_apparel;

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

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(2000) NOT NULL DEFAULT '',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL DEFAULT 'black-tshirt-placeholder.svg',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    status ENUM('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
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

-- Demo admin account. Change this password after first login.
-- Email: admin@gonzagas.local
-- Password: Admin@123!
INSERT INTO users (name,email,password_hash,role,status) VALUES
('Gonzaga Admin','admin@gonzagas.local','$2y$12$01y7sVTKeYO36ie5lZjJzOIUBrWczUB.E.q2RFJ7BhfLfFLvd/Uta','admin','active');

INSERT INTO products (name,description,price,stock,image,is_active) VALUES
('ESSENTIAL BLACK T-SHIRT','Minimal everyday essential.',899.00,25,'black-tshirt-placeholder.svg',1),
('ESSENTIAL WHITE T-SHIRT','Clean, versatile everyday essential.',899.00,25,'white-tshirt-placeholder.svg',1),
('MINIMAL HOODIE','Comfort-first minimal hoodie.',1599.00,15,'hoodie-placeholder.svg',1),
('CLASSIC CAP','Timeless cap for everyday styling.',699.00,20,'cap-placeholder.svg',1),
('LOGO TOTE BAG','Simple tote for everyday carry.',499.00,30,'tote-placeholder.svg',1);
