<?php
declare(strict_types=1);
require_once __DIR__ . '/functions/helpers.php';
$q = clean_text((string)($_GET['q'] ?? ''));
$category = (int)($_GET['category'] ?? 0);
$categories = db()->query('SELECT id,name,slug FROM categories WHERE is_active=1 ORDER BY name')->fetchAll();
$sql='SELECT p.id,p.name,p.price,p.stock,p.image,c.name AS category_name FROM products p LEFT JOIN categories c 
ON c.id=p.category_id WHERE p.is_active=1'; $params=[];
if($q!==''){ $sql.=' AND (p.name LIKE ? OR p.description LIKE ?)'; $like='%'.$q.'%'; $params=[$like,$like]; }
if($category>0){ $sql.=' AND p.category_id=?'; $params[]=$category; }
$sql.=' ORDER BY p.id ASC';
$stmt=db()->prepare($sql);$stmt->execute($params);$products=$stmt->fetchAll(); $flash=get_flash();
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Collection | Gonzaga's Apparel</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header class="site-header">
            <div class="header-inner container">
                <a class="brand brand--header" href="index.php">
                    <img class="logo-placeholder logo-placeholder--header" src="assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel">
                </a>
                <nav class="site-nav collection-nav">
                    <a class="nav-link" href="index.php">HOME</a>
                    <?php if(is_logged_in() && ($_SESSION['role']??'')==='customer'):?>
                        <a class="nav-link" href="customer/profile.php">PROFILE</a>
                        <?php endif;?>
                        <a class="nav-link" href="customer/cart.php">BAG (<?=cart_count()?>)</a>
                    </nav>
                </div>
            </header>
            <?php if($flash):?>
                <div class="site-flash <?=e($flash['type'])?>"><?=e($flash['message'])?>
            </div>
            <?php endif;?>
            <main class="shop-shell collection-shell">
                <div class="section-kicker">COLLECTION <span></span>
            </div>
            <h1>SHOP ALL</h1>
            <form class="collection-filters" method="get">
                <input type="search" name="q" value="<?=e($q)?>" placeholder="Search products...">
                <select name="category">
                    <option value="0">All categories</option>
                    <?php foreach($categories as $c):?>
                        <option value="<?=intval($c['id'])?>" <?=$category==intval($c['id'])?'selected':''?>><?=e($c['name'])?>
                    </option>
                    <?php endforeach;?>
                </select>
                <button class="button button--filled" type="submit">SEARCH</button>
                <?php if($q!==''||$category>0):?>
                    <a class="button button--outline" href="collection.php">CLEAR</a>
                    <?php endif;?>
                </form>
                <div class="product-grid collection-grid"><?php foreach($products as $product):?>
                    <article class="product-card">
                        <div class="product-image-wrap">
                            <img src="assets/images/<?=e($product['image'])?>" alt="<?=e($product['name'])?>">
                        </div>
                        <small class="product-category"><?=e($product['category_name']??'Uncategorized')?></small>
                        <h3><?=e($product['name'])?></h3>
                        <p class="product-price"><?=money((float)$product['price'])?></p>
                        <form action="actions/cart_process.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                            <input type="hidden" name="product_id" value="<?=intval($product['id'])?>">
                            <input type="hidden" name="action" value="add">
                            <button class="product-add" type="submit" <?=((int)$product['stock']<1?'disabled':'')?>><?=((int)$product['stock']<1?'OUT OF STOCK':'ADD TO BAG')?>
                            </button>
                        </form>
                    </article>
                    <?php endforeach;?>
                </div>
                <?php if(!$products):?>
                    <div class="empty-state">
                        <p>No products matched your search.</p>
                    </div>
                    <?php endif;?>
                </main>
            </body>
            
        </html>
