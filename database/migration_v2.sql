USE gonzagas_apparel;

CREATE TABLE IF NOT EXISTS profiles (
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

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(500) NOT NULL DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name,slug,description) VALUES
('T-Shirts','t-shirts','Essential everyday tops.'),
('Hoodies','hoodies','Comfort-first layers.'),
('Accessories','accessories','Caps, bags, and everyday carry pieces.');

ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED NULL AFTER id;
ALTER TABLE products ADD INDEX IF NOT EXISTS idx_products_category (category_id);
ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE products SET category_id=(SELECT id FROM categories WHERE slug='t-shirts') WHERE name LIKE '%T-SHIRT%' AND category_id IS NULL;
UPDATE products SET category_id=(SELECT id FROM categories WHERE slug='hoodies') WHERE name LIKE '%HOODIE%' AND category_id IS NULL;
UPDATE products SET category_id=(SELECT id FROM categories WHERE slug='accessories') WHERE category_id IS NULL;

ALTER TABLE orders ADD COLUMN IF NOT EXISTS subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_amount;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER subtotal_amount;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod' AFTER status;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_name VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_phone VARCHAR(30) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_address1 VARCHAR(190) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_address2 VARCHAR(190) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_city VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_province VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_postal_code VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_country VARCHAR(100) NOT NULL DEFAULT 'Philippines';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes VARCHAR(500) NOT NULL DEFAULT '';

UPDATE orders SET subtotal_amount=total_amount WHERE subtotal_amount=0 AND shipping_fee=0;
INSERT IGNORE INTO profiles (user_id) SELECT id FROM users;
