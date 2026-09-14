<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request.');
    redirect('admin/dashboard.php');
}

$id = (int)($_POST['id'] ?? 0);
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
    $product = [
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock' => $stock,
        'image' => $image,
        'category_id' => $categoryId,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    require __DIR__ . '/product_form.php';
    exit;
}

$stmt = db()->prepare('UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image = ?, is_active = ? WHERE id = ?');
$stmt->execute([(int)$categoryId, $name, $description, (float)$price, (int)$stock, $image, isset($_POST['is_active']) ? 1 : 0, $id]);
set_flash('success', 'Product updated successfully.');
redirect('admin/dashboard.php');
