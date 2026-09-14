<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid checkout request.');
    redirect('customer/checkout.php');
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    set_flash('error', 'Your bag is empty.');
    redirect('customer/cart.php');
}

$data = [];
foreach (['name','phone','address_line1','address_line2','city','province','postal_code','country','payment_method','notes','payment_reference'] as $key) {
    $data[$key] = clean_text((string)($_POST[$key] ?? ''));
}

$errors = validate_checkout($data);

// GCash receipt validation & upload handling
$receiptFilename = null;
if ($data['payment_method'] === 'gcash') {
    if (empty($_FILES['payment_receipt']) || $_FILES['payment_receipt']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please upload a picture or screenshot of your GCash payment receipt.';
    } else {
        $file = $_FILES['payment_receipt'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading payment receipt. Please try again.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowedExts, true)) {
                $errors[] = 'Invalid receipt format. Please upload a JPG, PNG, or WEBP image.';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $errors[] = 'Receipt file size is too large (max 10MB).';
            } else {
                $targetDir = __DIR__ . '/../assets/uploads/receipts/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $receiptFilename = 'gcash_receipt_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $targetDir . $receiptFilename;
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    $errors[] = 'Failed to save the uploaded receipt image.';
                    $receiptFilename = null;
                }
            }
        }
    }
}

if ($errors) {
    // If receipt was uploaded before another error, clean it up
    if ($receiptFilename && file_exists(__DIR__ . '/../assets/uploads/receipts/' . $receiptFilename)) {
        @unlink(__DIR__ . '/../assets/uploads/receipts/' . $receiptFilename);
    }
    $_SESSION['checkout_errors'] = $errors;
    $_SESSION['checkout_old'] = $data;
    redirect('customer/checkout.php');
}

$pdo = db();
$pdo->beginTransaction();
try {
    $ids = array_keys($cart);
    if (!$ids) throw new RuntimeException('Your bag is empty.');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $productStmt = $pdo->prepare("SELECT id, name, price, stock, image, is_active FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $productStmt->execute(array_map('intval', $ids));
    $products = [];
    foreach ($productStmt->fetchAll() as $row) {
        $products[(int)$row['id']] = $row;
    }

    $subtotal = 0.0;
    $items = [];
    foreach ($cart as $id => $qty) {
        $id = (int)$id;
        $qty = (int)$qty;
        if (!isset($products[$id]) || !(int)$products[$id]['is_active'] || $qty < 1 || $qty > (int)$products[$id]['stock']) {
            throw new RuntimeException('A product in your bag is unavailable or does not have enough stock.');
        }
        $price = (float)$products[$id]['price'];
        $line = $price * $qty;
        $subtotal += $line;
        $items[] = [
            'product' => $products[$id],
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $line
        ];
    }

    $shipping = $subtotal >= 2500 ? 0.00 : 150.00;
    $total = $subtotal + $shipping;

    $paymentStatus = 'pending';
    $driverConfirmation = 'unconfirmed';

    $orderStmt = $pdo->prepare('INSERT INTO orders 
        (user_id, total_amount, subtotal_amount, shipping_fee, status, payment_method, payment_receipt, payment_reference, payment_status, driver_confirmation, shipping_name, shipping_phone, shipping_address1, shipping_address2, shipping_city, shipping_province, shipping_postal_code, shipping_country, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    
    $orderStmt->execute([
        $_SESSION['user_id'],
        $total,
        $subtotal,
        $shipping,
        'pending',
        $data['payment_method'],
        $receiptFilename,
        $data['payment_reference'],
        $paymentStatus,
        $driverConfirmation,
        $data['name'],
        $data['phone'],
        $data['address_line1'],
        $data['address_line2'],
        $data['city'],
        $data['province'],
        $data['postal_code'],
        $data['country'],
        $data['notes']
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    foreach ($items as $item) {
        $itemStmt->execute([$orderId, $item['product']['id'], $item['product']['name'], $item['price'], $item['qty'], $item['subtotal']]);
        $stockStmt->execute([$item['qty'], $item['product']['id']]);
    }

    $profileStmt = $pdo->prepare('INSERT INTO profiles (user_id, phone, address_line1, address_line2, city, province, postal_code, country) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE phone=VALUES(phone), address_line1=VALUES(address_line1), address_line2=VALUES(address_line2), city=VALUES(city), province=VALUES(province), postal_code=VALUES(postal_code), country=VALUES(country)');
    $profileStmt->execute([$_SESSION['user_id'], $data['phone'], $data['address_line1'], $data['address_line2'], $data['city'], $data['province'], $data['postal_code'], $data['country']]);

    $pdo->commit();
    $_SESSION['cart'] = [];
    set_flash('success', 'Order #' . $orderId . ' placed successfully.');
    redirect('customer/order.php?id=' . $orderId);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($receiptFilename && file_exists(__DIR__ . '/../assets/uploads/receipts/' . $receiptFilename)) {
        @unlink(__DIR__ . '/../assets/uploads/receipts/' . $receiptFilename);
    }
    set_flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Unable to place your order right now.');
    redirect('customer/checkout.php');
}
