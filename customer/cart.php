<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0.0;

if ($cart) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT id, name, price, stock, image FROM products WHERE id IN ($placeholders) AND is_active = 1");
    $stmt->execute(array_map('intval', $ids));
    foreach ($stmt->fetchAll() as $product) {
        $qty = max(0, (int) ($cart[$product['id']] ?? 0));
        $qty = min($qty, (int) $product['stock']);
        if ($qty < 1) continue;
        $subtotal = (float) $product['price'] * $qty;
        $product['qty'] = $qty;
        $product['subtotal'] = $subtotal;
        $items[] = $product;
        $total += $subtotal;
    }
}

$flash = get_flash();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Shopping Bag | Gonzaga's Apparel</title><link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/auth.css"></head>
<body class="shop-page">
<header class="mini-header"><a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a><nav><a href="../index.php">HOME</a><a href="../collection.php">SHOP</a><?php if (is_logged_in()): ?><form action="../logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button type="submit">LOG OUT</button></form><?php else: ?><a href="../login.php">LOGIN</a><?php endif; ?></nav></header>
<main class="shop-shell"><div class="section-kicker">YOUR BAG <span></span></div><h1>SHOPPING BAG</h1>
<?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if (!$items): ?><div class="empty-state"><p>Your bag is currently empty.</p><a class="button button--filled" href="../index.php#collection">SHOP COLLECTION</a></div>
<?php else: ?>
<div class="cart-list"><?php foreach ($items as $item): ?><article class="cart-item"><img src="<?= e(product_image_src($item['image'], '../')) ?>" alt="<?= e($item['name']) ?>"><div><h2><?= e($item['name']) ?></h2><p><?= money((float)$item['price']) ?> × <?= $item['qty'] ?></p><strong><?= money($item['subtotal']) ?></strong></div><form action="../actions/cart_process.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="action" value="remove"><button type="submit" class="text-button">REMOVE</button></form></article><?php endforeach; ?></div>
<div class="cart-summary"><span>TOTAL</span><strong><?= money($total) ?></strong><?php if (is_logged_in() && ($_SESSION['role'] ?? '') === 'customer'): ?><a class="button button--filled" href="checkout.php">CHECKOUT</a><?php else: ?><a class="button button--filled" href="../login.php">LOGIN TO CHECKOUT</a><?php endif; ?></div>
<?php endif; ?></main></body></html>
