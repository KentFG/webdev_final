<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('customer');

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT o.*, u.name AS account_name, u.email FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE o.id = ? AND o.user_id = ? LIMIT 1');
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('customer/orders.php');
}

$stmt = db()->prepare('SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ? ORDER BY oi.id');
$stmt->execute([$id]);
$items = $stmt->fetchAll();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Order #<?=intval($id)?> | Gonzaga's Apparel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body class="shop-page">
<header class="mini-header">
    <a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a>
    <nav>
        <a href="../index.php">HOME</a>
        <a href="orders.php">ORDERS</a>
        <a href="profile.php">PROFILE</a>
        <form action="../logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <button type="submit">LOG OUT</button>
        </form>
    </nav>
</header>

<main class="shop-shell">
    <div class="section-kicker">ORDER <span></span></div>
    <h1>ORDER #<?=intval($id)?></h1>

    <?php if ($flash): ?>
        <div class="alert <?=e($flash['type'])?>"><?=e($flash['message'])?></div>
    <?php endif; ?>

    <div class="order-meta">
        <span>STATUS <strong><?=e(strtoupper($order['status']))?></strong></span>
        <span>PAYMENT METHOD 
            <strong>
                <?php if ($order['payment_method'] === 'gcash'): ?>
                    <img src="../assets/icons/gcash.svg" alt="GCash" style="height:14px; width:14px; vertical-align:middle;"> GCASH
                <?php else: ?>
                    ?? CASH ON DELIVERY
                <?php endif; ?>
            </strong>
        </span>
        <span>ORDER DATE <strong><?=e($order['created_at'])?></strong></span>
    </div>

    <!-- Payment & Driver Confirmation Status Banner -->
    <?php if ($order['payment_method'] === 'cod'): ?>
        <div style="margin: 18px 0; padding: 16px; border: 1px solid #ddd; background: #fafafa; border-radius: 4px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="font-size:13px; font-weight:700; margin-bottom:4px;">?? DRIVER PAYMENT CONFIRMATION</h3>
                    <?php if (($order['driver_confirmation'] ?? 'unconfirmed') === 'confirmed'): ?>
                        <p style="font-size:12px; color:#2e7d32;">
                            ? <strong>Confirmed by Driver:</strong> <?=e($order['driver_name'] ?: 'Assigned Delivery Driver')?>
                            <?php if (!empty($order['driver_confirmed_at'])): ?>
                                &bull; <?=e(date('M d, Y h:i A', strtotime($order['driver_confirmed_at'])))?>
                            <?php endif; ?>
                            <br><small style="color:#555;">Payment of <?=money((float)$order['total_amount'])?> has been received and confirmed by the driver.</small>
                        </p>
                    <?php else: ?>
                        <p style="font-size:12px; color:#c67d00;">
                            ? <strong>Pending Driver Confirmation:</strong> Please hand the cash payment of <strong><?=money((float)$order['total_amount'])?></strong> to the driver upon delivery. The driver will confirm receipt on their device.
                        </p>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (($order['driver_confirmation'] ?? 'unconfirmed') === 'confirmed'): ?>
                        <span class="driver-confirm-badge confirmed">? DRIVER CONFIRMED</span>
                    <?php else: ?>
                        <span class="driver-confirm-badge unconfirmed">? PENDING DRIVER CONFIRMATION</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($order['payment_method'] === 'gcash'): ?>
        <div style="margin: 18px 0; padding: 16px; border: 1px solid #b3d4fc; background: #f0f6ff; border-radius: 4px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
                <div>
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <img src="../assets/icons/gcash.svg" alt="GCash" style="height:24px; width:auto; vertical-align:middle;">
                        <span style="font-size:12px; font-weight:700; color:#005ce6;">PAYMENT RECEIPT CONFIRMATION</span>
                    </div>
                    <p style="font-size:12px; color:#333;">
                        Payment Status: 
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <strong style="color:#2e7d32;">? VERIFIED & CONFIRMED</strong>
                        <?php else: ?>
                            <strong style="color:#005ce6;">RECEIPT SUBMITTED (UNDER VERIFICATION)</strong>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($order['payment_reference'])): ?>
                        <p style="font-size:11px; color:#555; margin-top:2px;">
                            Reference No: <strong><?=e($order['payment_reference'])?></strong>
                        </p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($order['payment_receipt'])): ?>
                    <div style="text-align:center;">
                        <span style="font-size:10px; font-weight:600; color:#666; display:block; margin-bottom:4px;">ATTACHED RECEIPT</span>
                        <a href="../assets/uploads/receipts/<?=e($order['payment_receipt'])?>" target="_blank" class="receipt-thumb-link" title="Click to view full receipt">
                            <img src="../assets/uploads/receipts/<?=e($order['payment_receipt'])?>" alt="GCash Payment Receipt" class="receipt-thumb-img">
                            <small style="display:block; font-size:9px; color:#005ce6; margin-top:2px;">?? Click to enlarge</small>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>PRODUCT</th>
                    <th>PRICE</th>
                    <th>QTY</th>
                    <th>SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?=e(product_image_src($item['image'], '../'))?>" alt="<?=e($item['product_name'])?>" style="width:36px; height:36px; object-fit:cover; border:1px solid #ddd;">
                                <?php endif; ?>
                                <strong><?=e($item['product_name'])?></strong>
                            </div>
                        </td>
                        <td><?=money((float)$item['unit_price'])?></td>
                        <td><?=intval($item['quantity'])?></td>
                        <td><?=money((float)$item['subtotal'])?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="order-total">
        <span>SUBTOTAL <?=money((float)$order['subtotal_amount'])?></span>
        <span>SHIPPING <?=$order['shipping_fee'] > 0 ? money((float)$order['shipping_fee']) : 'FREE'?></span>
        <strong>TOTAL <?=money((float)$order['total_amount'])?></strong>
    </div>

    <section class="address-card">
        <h2>SHIPPING TO</h2>
        <p>
            <strong><?=e($order['shipping_name'])?></strong><br>
            <?=e($order['shipping_phone'])?><br>
            <?=e($order['shipping_address1'])?><br>
            <?=($order['shipping_address2'] ? e($order['shipping_address2']) . '<br>' : '')?>
            <?=e($order['shipping_city'])?>, <?=e($order['shipping_province'])?> <?=e($order['shipping_postal_code'])?><br>
            <?=e($order['shipping_country'])?>
        </p>
        <?php if ($order['notes']): ?>
            <p><strong>Notes:</strong> <?=e($order['notes'])?></p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
