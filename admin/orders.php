<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

$status = clean_text((string)($_GET['status'] ?? ''));
$allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

$sql = "SELECT o.id, o.total_amount, o.status, o.payment_method, o.payment_status, o.driver_confirmation, o.driver_name, o.payment_receipt, o.created_at, u.name, u.email 
        FROM orders o 
        INNER JOIN users u ON u.id = o.user_id";
$params = [];
if (in_array($status, $allowed, true)) {
    $sql .= ' WHERE o.status = ?';
    $params[] = $status;
}
$sql .= ' ORDER BY o.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Manage Orders | Gonzaga's Apparel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-page">
<header class="admin-header">
    <a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a>
    <div>
        <a href="dashboard.php">DASHBOARD</a>
        <form action="../logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <button type="submit">LOG OUT</button>
        </form>
    </div>
</header>

<main class="admin-shell">
    <div class="section-kicker">ORDER MANAGEMENT <span></span></div>
    <h1>MANAGE ORDERS</h1>

    <?php if ($flash): ?>
        <div class="alert <?=e($flash['type'])?>"><?=e($flash['message'])?></div>
    <?php endif; ?>

    <form class="admin-filter" method="get">
        <label>
            FILTER BY STATUS
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach ($allowed as $s): ?>
                    <option value="<?=e($s)?>" <?=$status === $s ? 'selected' : ''?>><?=e(ucfirst($s))?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button button--filled" type="submit">FILTER</button>
        <?php if ($status !== ''): ?>
            <a class="button button--outline" href="orders.php">CLEAR</a>
        <?php endif; ?>
    </form>

    <section class="admin-card">
        <div class="table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th class="col-order">ORDER</th>
                        <th class="col-customer">CUSTOMER</th>
                        <th class="col-date">DATE</th>
                        <th class="col-total">TOTAL</th>
                        <th class="col-payment">PAYMENT / CONFIRMATION</th>
                        <th class="col-status">STATUS</th>
                        <th class="col-action">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$orders): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                No orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="col-order">
                                <a class="table-link" href="order.php?id=<?=intval($order['id'])?>">
                                    <strong>#<?=intval($order['id'])?></strong>
                                </a>
                            </td>
                            <td class="col-customer">
                                <strong><?=e($order['name'])?></strong>
                                <small><?=e($order['email'])?></small>
                            </td>
                            <td class="col-date">
                                <?=e(date('M d, Y h:i A', strtotime($order['created_at'])))?>
                            </td>
                            <td class="col-total">
                                <span style="font-size:14px; font-weight:700;"><?=money((float)$order['total_amount'])?></span>
                            </td>
                            <td class="col-payment">
                                <?php if ($order['payment_method'] === 'gcash'): ?>
                                    <div>
                                        <span style="font-weight:700; color:#005ce6; display:inline-flex; align-items:center; gap:4px;">
                                            <img src="../assets/icons/gcash.svg" alt="GCash" style="height:14px; width:14px;"> GCash
                                        </span>
                                        <br>
                                        <?php if ($order['payment_status'] === 'paid'): ?>
                                            <span class="driver-pill gcash-verified">? Verified & Paid</span>
                                        <?php else: ?>
                                            <span class="driver-pill unconfirmed">? Receipt Attached</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <span style="font-weight:600;">?? Cash on Delivery</span>
                                        <br>
                                        <?php if (($order['driver_confirmation'] ?? 'unconfirmed') === 'confirmed'): ?>
                                            <span class="driver-pill confirmed">? Driver Confirmed</span>
                                        <?php else: ?>
                                            <span class="driver-pill unconfirmed">? Awaiting Driver</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="col-status">
                                <span class="status-badge <?=e($order['status'])?>">
                                    <?=e(strtoupper($order['status']))?>
                                </span>
                            </td>
                            <td class="col-action">
                                <a class="button button--outline" href="order.php?id=<?=intval($order['id'])?>">VIEW ORDER</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
