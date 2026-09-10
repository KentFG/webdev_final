# Gonzaga's Apparel Web Project

PHP/MySQL e-commerce project based on the Gonzaga's Apparel homepage mockup.

## Setup
1. Extract into `htdocs/gonzagas-apparel-web`.
2. Start Apache and MySQL in XAMPP.
3. For a fresh database, import `database/schema.sql`; for an existing project database, use `database/migration_v2.sql` instead.
4. Open `http://localhost/gonzagas-apparel-web/`.

## Main features
- Guest browsing and shopping bag.
- Customer signup/login/logout with secure password hashing.
- Session-based role recognition: guest, customer, admin.
- Password validation in `functions/validation.php`.
- Product categories and keyword search in `collection.php`.
- Admin product/category management.
- Customer profile and saved shipping information.
- Complete checkout flow with shipping details, payment method selection, shipping fee calculation, transaction-safe stock decrement, and order confirmation.
- Customer order history and order details.
- Admin order filtering, order detail view, status management, and safe stock restoration when cancelling/reopening orders.
- PDO prepared statements, CSRF protection, validation, and friendly error handling.

## SVG replacement points
- Hero image: `assets/images/hero-placeholder.svg`
- Header/footer/logo image: `assets/images/logo-placeholder.svg`

Replace those SVG files with your own SVG artwork while keeping the filenames, or update the corresponding `<img src>` paths.

## Demo admin
Email: `admin@gonzagas.local`
Password: `Admin@123!`

Change the demo password after setup.
