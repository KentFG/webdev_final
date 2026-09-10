<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('customer');
$stmt = db()->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>My Orders | Gonzaga's Apparel</title><link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/auth.css"></head>
<body class="shop-page"><header class="mini-header"><a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a><nav><a href="../index.php">HOME</a><a href="../collection.php">SHOP</a><a href="cart.php">BAG (<?= cart_count() ?>)</a><a href="profile.php">PROFILE</a><form action="../logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button type="submit">LOG OUT</button></form></nav></header><main class="shop-shell"><div class="section-kicker">ACCOUNT <span></span></div><h1>MY ORDERS</h1><?php if (!$orders): ?><div class="empty-state"><p>You have not placed any orders yet.</p><a class="button button--filled" href="../index.php#collection">SHOP COLLECTION</a></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>ORDER</th><th>DATE</th><th>STATUS</th><th>TOTAL</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><a class="table-link" href="order.php?id=<?= (int)$order['id'] ?>">#<?= (int)$order['id'] ?></a></td><td><?= e($order['created_at']) ?></td><td><?= e(strtoupper($order['status'])) ?></td><td><?= money((float)$order['total_amount']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></main></body></html>
