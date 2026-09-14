<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT o.*, u.name AS account_name, u.email FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE o.id = ? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('admin/orders.php');
}

$stmt = db()->prepare('SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ? ORDER BY oi.id');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$statusOptions = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Order #<?=intval($id)?> | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-page">
<header class="admin-header">
    <a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a>
    <div>
        <a href="dashboard.php">DASHBOARD</a>
        <a href="orders.php">ORDERS</a>
        <form action="../logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <button type="submit">LOG OUT</button>
        </form>
    </div>
</header>

<main class="admin-shell">
    <div class="section-kicker">ORDER DETAILS <span></span></div>
    <h1>ORDER #<?=intval($id)?></h1>

    <?php if ($flash): ?>
        <div class="alert <?=e($flash['type'])?>"><?=e($flash['message'])?></div>
    <?php endif; ?>

    <section class="admin-card order-detail-grid">
        <div>
            <h2>CUSTOMER & SHIPPING</h2>
            <p>
                <strong><?=e($order['account_name'])?></strong><br>
                <?=e($order['email'])?><br>
                <?=e($order['shipping_phone'])?>
            </p>
            <p>
                <strong>Address:</strong><br>
                <?=e($order['shipping_address1'])?><br>
                <?=($order['shipping_address2'] ? e($order['shipping_address2']) . '<br>' : '')?>
                <?=e($order['shipping_city'])?>, <?=e($order['shipping_province'])?> <?=e($order['shipping_postal_code'])?><br>
                <?=e($order['shipping_country'])?>
            </p>
        </div>

        <div>
            <h2>STATUS & PAYMENT</h2>
            <form action="order_update.php" method="post" class="inline-form" style="margin-bottom:14px;">
                <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                <input type="hidden" name="id" value="<?=$id?>">
                <input type="hidden" name="action" value="update_status">
                <select name="status">
                    <?php foreach ($statusOptions as $s): ?>
                        <option value="<?=e($s)?>" <?=$order['status'] === $s ? 'selected' : ''?>><?=e(ucfirst($s))?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button button--filled" type="submit">UPDATE STATUS</button>
            </form>

            <p style="margin-top:10px; line-height:1.6;">
                Payment Method: 
                <strong>
                    <?php if ($order['payment_method'] === 'gcash'): ?>
                        <img src="../assets/icons/gcash.svg" alt="GCash" style="height:14px; width:14px; vertical-align:middle;"> GCash (E-Wallet)
                    <?php else: ?>
                        ?? Cash on Delivery (COD)
                    <?php endif; ?>
                </strong><br>
                Placed on: <?=e($order['created_at'])?>
            </p>
        </div>
    </section>

    <!-- Payment Verification / Driver Confirmation Section -->
    <section class="admin-card" style="border-left: 4px solid <?=($order['payment_method'] === 'gcash' ? '#005ce6' : '#111')?>;">
        <?php if ($order['payment_method'] === 'cod'): ?>
            <div class="admin-card-head">
                <h2>?? CASH ON DELIVERY: DRIVER PAYMENT CONFIRMATION</h2>
                <?php if (($order['driver_confirmation'] ?? 'unconfirmed') === 'confirmed'): ?>
                    <span class="driver-confirm-badge confirmed" style="font-size:12px; padding:6px 12px;">? DRIVER CONFIRMED</span>
                <?php else: ?>
                    <span class="driver-confirm-badge unconfirmed" style="font-size:12px; padding:6px 12px;">? AWAITING DRIVER CONFIRMATION</span>
                <?php endif; ?>
            </div>

            <?php if (($order['driver_confirmation'] ?? 'unconfirmed') === 'confirmed'): ?>
                <div style="background:#e8f5e9; padding:16px; border:1px solid #a5d6a7; border-radius:4px; font-size:12px;">
                    <strong style="color:#2e7d32; font-size:13px;">? Payment Received and Confirmed by Driver</strong><br>
                    <div style="margin-top:6px;">
                        Confirmed by: <strong><?=e($order['driver_name'] ?: 'Courier Driver')?></strong><br>
                        Timestamp: <strong><?=e($order['driver_confirmed_at'] ? date('M d, Y h:i A', strtotime($order['driver_confirmed_at'])) : 'N/A')?></strong><br>
                        <?php if (!empty($order['driver_notes'])): ?>
                            Driver Notes: <em><?=e($order['driver_notes'])?></em><br>
                        <?php endif; ?>
                        Total Amount Collected: <strong><?=money((float)$order['total_amount'])?></strong>
                    </div>
                </div>
            <?php else: ?>
                <p style="font-size:12px; color:#555; margin-bottom:12px;">
                    The customer chose Cash on Delivery. When the driver collects payment upon delivery, submit the confirmation below:
                </p>
                <form action="order_update.php" method="post" class="admin-form" style="padding:18px; background:#fafafa; border:1px solid #eee;">
                    <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                    <input type="hidden" name="id" value="<?=$id?>">
                    <input type="hidden" name="action" value="confirm_driver_payment">

                    <div class="form-grid">
                        <label>
                            DRIVER NAME / COURIER
                            <input name="driver_name" type="text" placeholder="e.g. John Doe / Lalamove / J&T Rider" required>
                        </label>
                        <label>
                            DELIVERY NOTES (OPTIONAL)
                            <input name="driver_notes" type="text" placeholder="e.g. Paid in exact cash, signed by recipient">
                        </label>
                    </div>

                    <button class="button button--filled" type="submit" style="margin-top:10px; background:#2e7d32; border-color:#2e7d32;">
                        ? CONFIRM PAYMENT RECEIVED BY DRIVER (<?=money((float)$order['total_amount'])?>)
                    </button>
                </form>
            <?php endif; ?>

        <?php elseif ($order['payment_method'] === 'gcash'): ?>
            <div class="admin-card-head">
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="../assets/icons/gcash.svg" alt="GCash" style="height:28px; width:auto; vertical-align:middle;">
                    <h2 style="margin:0;">GCASH PAYMENT RECEIPT VERIFICATION</h2>
                </div>
                <div>
                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <span class="driver-confirm-badge confirmed" style="font-size:12px; padding:6px 12px;">? GCASH VERIFIED & PAID</span>
                    <?php else: ?>
                        <span class="driver-confirm-badge unconfirmed" style="font-size:12px; padding:6px 12px;">? PENDING VERIFICATION</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:24px; align-items:start; margin-top:12px;">
                <div>
                    <p style="font-size:12px; line-height:1.7;">
                        Total Required: <strong style="font-size:14px;"><?=money((float)$order['total_amount'])?></strong><br>
                        GCash Reference No: <strong><?=e($order['payment_reference'] ?: 'None provided by customer')?></strong><br>
                        Payment Status: <strong><?=e(strtoupper($order['payment_status']))?></strong>
                    </p>

                    <?php if ($order['payment_status'] !== 'paid'): ?>
                        <form action="order_update.php" method="post" style="margin-top:14px;">
                            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                            <input type="hidden" name="id" value="<?=$id?>">
                            <input type="hidden" name="action" value="confirm_gcash_payment">
                            <button class="button button--filled" type="submit" style="background:#005ce6; border-color:#005ce6;">
                                ? VERIFY & CONFIRM GCASH RECEIPT (MARK AS PAID)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div style="border:1px solid #ddd; padding:12px; background:#fff; text-align:center; border-radius:4px;">
                    <strong style="font-size:11px; display:block; margin-bottom:8px; color:#444;">CUSTOMER'S UPLOADED RECEIPT PICTURE</strong>
                    <?php if (!empty($order['payment_receipt'])): ?>
                        <a href="../assets/uploads/receipts/<?=e($order['payment_receipt'])?>" target="_blank" title="Click to view full image in new tab">
                            <img src="../assets/uploads/receipts/<?=e($order['payment_receipt'])?>" alt="GCash Receipt" style="max-width:100%; max-height:300px; object-fit:contain; border:1px solid #eee; border-radius:2px;">
                        </a>
                        <small style="display:block; margin-top:6px; color:#005ce6;">?? Click image to open in full size</small>
                    <?php else: ?>
                        <p style="color:#999; font-size:11px; padding:20px;">No receipt picture uploaded.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Ordered Items Section -->
    <section class="admin-card">
        <h2>ORDERED ITEMS</h2>
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
                                        <img src="<?=e(product_image_src($item['image'], '../'))?>" alt="<?=e($item['product_name'])?>" style="width:40px; height:40px; object-fit:cover; border:1px solid #ddd;">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?=e($item['product_name'])?></strong>
                                        <?php if (!empty($item['product_id'])): ?>
                                            <small style="display:block; color:#777; font-size:9px;">Product ID: #<?=intval($item['product_id'])?></small>
                                        <?php endif; ?>
                                    </div>
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

        <?php if ($order['notes']): ?>
            <p style="margin-top:14px; font-size:12px; border-top:1px solid #eee; padding-top:10px;">
                <strong>Customer Notes:</strong> <?=e($order['notes'])?>
            </p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
