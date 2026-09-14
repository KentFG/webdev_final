<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $product = [
        'name' => '',
        'description' => '',
        'price' => '0.00',
        'stock' => '0',
        'image' => 'black-tshirt-placeholder.svg',
        'category_id' => '',
        'is_active' => 1
    ];
    $errors = [];
    require __DIR__ . '/product_form.php';
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid security token.');
    redirect('admin/dashboard.php');
}

$name = clean_text((string)($_POST['name'] ?? ''));
$description = clean_text((string)($_POST['description'] ?? ''));
$price = clean_text((string)($_POST['price'] ?? ''));
$stock = clean_text((string)($_POST['stock'] ?? ''));
$image = clean_text((string)($_POST['image'] ?? ''));
$categoryId = (string)($_POST['category_id'] ?? '');

// Handle real file upload if provided
if (!empty($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    if (in_array($ext, $allowedExts, true)) {
        $destDir = __DIR__ . '/../assets/images/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $newFilename = 'product_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $destDir . $newFilename)) {
            $image = $newFilename;
        }
    }
}

if ($image === '') {
    $image = 'black-tshirt-placeholder.svg';
}

$errors = validate_product($name, $description, $price, $stock, $image, $categoryId);
if ($errors) {
    $product = compact('name', 'description', 'price', 'stock', 'image');
    $product['category_id'] = $categoryId;
    $product['is_active'] = 1;
    require __DIR__ . '/product_form.php';
    exit;
}

$stmt = db()->prepare('INSERT INTO products (category_id, name, description, price, stock, image, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
$stmt->execute([(int)$categoryId, $name, $description, (float)$price, (int)$stock, $image]);
set_flash('success', 'Product created successfully.');
redirect('admin/dashboard.php');
