<?php
declare(strict_types=1);

require_once __DIR__ . '/../functions/helpers.php';

require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM products WHERE id=?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: dashboard.php');
    exit;
}

$errors = [];
require __DIR__ . '/product_form.php';