<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');

$products = db()->query('SELECT p.id, p.name, p.description, p.price, p.stock, p.image, p.is_active, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON c.id = p.category_id 
    ORDER BY p.id DESC')->fetchAll();

$categories = db()->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$orderCount = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$userCount = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$revenue = (float) db()->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin Dashboard | Gonzaga's Apparel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-page">
<header class="admin-header">
    <a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a>
    <div>
        <span>ADMIN: <?=e($_SESSION['name'] ?? '')?></span>
        <form action="../logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <button type="submit">LOG OUT</button>
        </form>
    </div>
</header>

<main class="admin-shell">
    <div class="section-kicker">MANAGEMENT <span></span></div>
    <h1>ADMIN DASHBOARD</h1>

    <?php if ($flash): ?>
        <div class="alert <?=e($flash['type'])?>"><?=e($flash['message'])?></div>
    <?php endif; ?>

    <div class="stats">
        <div>
            <strong><?=$userCount?></strong>
            <span>CUSTOMERS</span>
        </div>
        <div>
            <strong><?=$orderCount?></strong>
            <span>ORDERS</span>
        </div>
        <div>
            <strong><?=money($revenue)?></strong>
            <span>REVENUE</span>
        </div>
    </div>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2>PRODUCTS</h2>
            <div class="admin-card-actions">
                <a class="button button--outline" href="orders.php">MANAGE ORDERS</a>
                <a class="button button--filled" href="product_create.php">+ ADD PRODUCT</a>
            </div>
        </div>

        <div class="table-wrap">
            <table class="products-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-product">PRODUCT</th>
                        <th class="col-category">CATEGORY</th>
                        <th class="col-price">PRICE</th>
                        <th class="col-stock">STOCK</th>
                        <th class="col-active">ACTIVE</th>
                        <th class="col-actions">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$products): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                No products found. Click "+ ADD PRODUCT" to create one.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="col-id">
                                #<?=(int)$product['id']?>
                            </td>
                            <td class="col-product">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <img src="<?=e(product_image_src($product['image'], '../'))?>" alt="<?=e($product['name'])?>" style="width: 44px; height: 44px; object-fit: cover; border: 1px solid #ddd; background: #fff; border-radius: 2px;">
                                    <div>
                                        <strong><?=e($product['name'])?></strong>
                                        <small><?=e($product['image'])?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="col-category">
                                <?=e($product['category_name'] ?? 'Uncategorized')?>
                            </td>
                            <td class="col-price">
                                <strong><?=money((float)$product['price'])?></strong>
                            </td>
                            <td class="col-stock">
                                <?php $stock = (int)$product['stock']; ?>
                                <span style="<?=$stock < 5 ? 'color: #d32f2f; font-weight:700;' : ''?>">
                                    <?=$stock?> in stock
                                </span>
                            </td>
                            <td class="col-active">
                                <span class="active-pill <?=$product['is_active'] ? 'yes' : 'no'?>">
                                    <?=$product['is_active'] ? 'YES' : 'NO'?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <div class="actions">
                                    <a href="product_edit.php?id=<?=(int)$product['id']?>">EDIT</a>
                                    <form action="product_delete.php" method="post" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                                        <input type="hidden" name="id" value="<?=(int)$product['id']?>">
                                        <button type="submit">DELETE</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card category-manager">
        <div class="admin-card-head">
            <h2>CATEGORIES</h2>
        </div>
        <form class="category-form" action="category_create.php" method="post">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <input name="name" maxlength="100" placeholder="New category name" required>
            <input name="description" maxlength="500" placeholder="Short description">
            <button class="button button--filled" type="submit">ADD CATEGORY</button>
        </form>
        <div class="category-list">
            <?php foreach ($categories as $category): ?>
                <span><?=e($category['name'])?></span>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
