<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

$id = isset($product['id']) ? (int)$product['id'] : 0;
$action = $id ? 'product_update.php' : 'product_create.php';
$title = $id ? 'EDIT PRODUCT' : 'ADD PRODUCT';
$categories = $categories ?? db()->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?=e($title)?> | Gonzaga's Apparel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-page">
<header class="admin-header">
    <a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a>
    <div><a href="dashboard.php">DASHBOARD</a></div>
</header>

<main class="admin-shell narrow">
    <div class="section-kicker">PRODUCT MANAGEMENT <span></span></div>
    <h1><?=e($title)?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?=e($error)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="admin-form" action="<?=e($action)?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
        <?php if ($id): ?>
            <input type="hidden" name="id" value="<?=$id?>">
        <?php endif; ?>

        <label>
            NAME
            <input name="name" type="text" maxlength="150" value="<?=e($product['name'] ?? '')?>" required placeholder="e.g. Minimalist Graphic Tee">
        </label>

        <label>
            CATEGORY
            <select name="category_id" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?=intval($category['id'])?>" <?=intval($product['category_id'] ?? 0) === intval($category['id']) ? 'selected' : ''?>>
                        <?=e($category['name'])?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            DESCRIPTION
            <textarea name="description" rows="4" maxlength="2000" placeholder="Product details, fabric, sizing..."><?=e($product['description'] ?? '')?></textarea>
        </label>

        <div class="form-grid">
            <label>
                PRICE (?)
                <input name="price" type="number" min="0" step="0.01" value="<?=e((string)($product['price'] ?? '0.00'))?>" required>
            </label>
            <label>
                STOCK QUANTITY
                <input name="stock" type="number" min="0" step="1" value="<?=e((string)($product['stock'] ?? '0'))?>" required>
            </label>
        </div>

        <!-- Functional Image Upload & Path -->
        <div style="border: 1px dashed #bbb; padding: 18px; background: #fafafa; border-radius: 4px;">
            <label style="margin-bottom: 6px;">
                UPLOAD PRODUCT IMAGE FILE
                <input type="file" name="image_file" id="image_file" accept="image/*" onchange="previewProductFile(event)">
            </label>
            <small style="color: #666; display: block; font-size: 10px; margin-bottom: 12px;">
                Supported formats: JPG, PNG, WEBP, SVG, GIF. Uploading a file will automatically save it to assets/images/.
            </small>

            <label style="margin-bottom: 6px;">
                OR IMAGE PATH / FILENAME
                <input name="image" id="image_input" type="text" value="<?=e($product['image'] ?? 'black-tshirt-placeholder.svg')?>" placeholder="e.g. black-tshirt-placeholder.svg or https://..." oninput="previewProductPath(this.value)">
            </label>

            <div style="display: flex; align-items: center; gap: 14px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #eee;">
                <img id="product_preview_img" src="<?=e(product_image_src($product['image'] ?? 'black-tshirt-placeholder.svg', '../'))?>" alt="Product Preview" style="width: 72px; height: 72px; object-fit: cover; border: 1px solid #ccc; background: #fff;">
                <div>
                    <strong style="font-size: 11px; display: block;">Image Live Preview</strong>
                    <small style="color: #777; font-size: 10px;" id="preview_caption"><?=e($product['image'] ?? 'black-tshirt-placeholder.svg')?></small>
                </div>
            </div>
        </div>

        <?php if ($id): ?>
            <label class="check" style="margin-top: 6px;">
                <input name="is_active" type="checkbox" value="1" <?=!empty($product['is_active']) ? 'checked' : ''?>>
                Active product (visible in shop)
            </label>
        <?php endif; ?>

        <button class="button button--filled" type="submit" style="margin-top: 12px;">SAVE PRODUCT</button>
    </form>
</main>

<script>
function previewProductFile(event) {
    const file = event.target.files[0];
    const previewImg = document.getElementById('product_preview_img');
    const caption = document.getElementById('preview_caption');
    const textInput = document.getElementById('image_input');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            caption.textContent = 'Selected: ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

function previewProductPath(val) {
    const previewImg = document.getElementById('product_preview_img');
    const caption = document.getElementById('preview_caption');
    val = val.trim();
    if (!val) {
        val = 'black-tshirt-placeholder.svg';
    }
    if (val.startsWith('http://') || val.startsWith('https://')) {
        previewImg.src = val;
    } else if (val.startsWith('assets/')) {
        previewImg.src = '../' + val;
    } else {
        previewImg.src = '../assets/images/' + val;
    }
    caption.textContent = val;
}
</script>
</body>
</html>
