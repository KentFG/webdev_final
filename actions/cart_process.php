<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request.');
    header('Location: ' . url('index.php'));
    exit;
}

$id = (int) ($_POST['product_id'] ?? 0);
$action = $_POST['action'] ?? '';
$stmt = db()->prepare('SELECT id, stock, is_active FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product || !(int)$product['is_active']) {
    set_flash('error', 'That product is no longer available.');
    header('Location: ' . url('index.php#collection'));
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$current = (int) ($_SESSION['cart'][$id] ?? 0);

if ($action === 'add') {
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));
    $_SESSION['cart'][$id] = min((int)$product['stock'], $current + $qty);
    set_flash('success', 'Product added to your bag.');
} elseif ($action === 'remove') {
    unset($_SESSION['cart'][$id]);
    set_flash('success', 'Product removed from your bag.');
}

$back = $_SERVER['HTTP_REFERER'] ?? url('index.php#collection');
// If coming from the homepage without a hash, anchor to #collection
if (preg_match('/index\.php\s*$/', $back) || preg_match('/gonzagas-apparel-web\/?$/', $back)) {
    $back = rtrim(preg_replace('/#.*$/', '', $back), '/') . '#collection';
}
header('Location: ' . $back);
exit;
