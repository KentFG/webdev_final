<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request.');
    redirect('admin/orders.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    set_flash('error', 'Invalid order ID.');
    redirect('admin/orders.php');
}

$action = clean_text((string)($_POST['action'] ?? 'update_status'));
$pdo = db();

if ($action === 'confirm_driver_payment') {
    $driverName = clean_text((string)($_POST['driver_name'] ?? 'Assigned Courier Driver'));
    $driverNotes = clean_text((string)($_POST['driver_notes'] ?? ''));
    if ($driverName === '') $driverName = 'Assigned Courier Driver';

    $stmt = $pdo->prepare('UPDATE orders SET driver_confirmation = "confirmed", driver_confirmed_at = NOW(), driver_name = ?, driver_notes = ?, payment_status = "confirmed_by_driver" WHERE id = ?');
    $stmt->execute([$driverName, $driverNotes, $id]);
    set_flash('success', 'Driver payment confirmation recorded for Order #' . $id . '.');
    redirect('admin/order.php?id=' . $id);
}

if ($action === 'confirm_gcash_payment') {
    $stmt = $pdo->prepare('UPDATE orders SET payment_status = "paid" WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'GCash payment confirmed & verified for Order #' . $id . '.');
    redirect('admin/order.php?id=' . $id);
}

// Otherwise: standard status update
$status = clean_text((string)($_POST['status'] ?? ''));
$allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
if (!in_array($status, $allowed, true)) {
    set_flash('error', 'Invalid order status.');
    redirect('admin/orders.php');
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) throw new RuntimeException('Order not found.');
    $old = $order['status'];

    if ($old !== 'cancelled' && $status === 'cancelled') {
        $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL');
        $items->execute([$id]);
        $update = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        foreach ($items->fetchAll() as $item) {
            $update->execute([(int)$item['quantity'], (int)$item['product_id']]);
        }
    } elseif ($old === 'cancelled' && $status !== 'cancelled') {
        $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL');
        $items->execute([$id]);
        $check = $pdo->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
        $update = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
        foreach ($items->fetchAll() as $item) {
            $check->execute([(int)$item['product_id']]);
            $product = $check->fetch();
            if (!$product || ((int)$product['stock'] < (int)$item['quantity'])) {
                throw new RuntimeException('Cannot reopen order because one or more products no longer have enough stock.');
            }
            $update->execute([(int)$item['quantity'], (int)$item['product_id']]);
        }
    }

    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    $pdo->commit();
    set_flash('success', 'Order #' . $id . ' status updated to ' . strtoupper($status) . '.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Unable to update the order.');
}

redirect('admin/order.php?id=' . $id);
