<?php
declare(strict_types=1);
require_once __DIR__ . '/functions/helpers.php';

$navItems = [
    ['label' => 'HOME', 'href' => '#home'], ['label' => 'ABOUT', 'href' => '#about'],
    ['label' => 'COLLECTION', 'href' => 'collection.php'], ['label' => 'REVIEWS', 'href' => '#lookbook'],
    ['label' => 'CONTACT', 'href' => '#contact'],
];
$values = [
    ['icon'=>'quality','title'=>'PREMIUM QUALITY','copy'=>'We use high-quality fabrics that are soft, durable, and built to last.'],
    ['icon'=>'comfort','title'=>'COMFORT FIRST','copy'=>'Designed for all-day comfort so you can move freely and feel your best.'],
    ['icon'=>'style','title'=>'TIMELESS STYLE','copy'=>'Minimal designs that never go out of style and easy to mix and match.'],
    ['icon'=>'care','title'=>'CARE IN EVERY ORDER','copy'=>'From packaging to delivery, we ensure a great experience every step of the way.'],
];
$testimonials = [
    ['quote'=>'The quality is amazing! Super comfortable and the design is clean and easy to style.','name'=>'Mark D.'],
    ['quote'=>'Finally found a local brand that focuses on both style and comfort. Highly recommended!','name'=>'Jessa M.'],
    ['quote'=>'Fast shipping and great service. The fit is perfect. Will definitely order again!','name'=>'Kyle R.'],
];
$products = db()->query('SELECT id,name,price,image,stock FROM products WHERE is_active=1 ORDER BY id ASC LIMIT 5')->fetchAll();
$quickLinks=['Home','About','Collection','Lookbook','Contact']; $careLinks=['FAQS','Shipping & Returns','Size Guide','Privacy Policy','Terms & Conditions'];
$flash=get_flash(); $user=current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="Gonzaga's Apparel - minimal clothing, quality everyday wear, and timeless essentials.">
<title>Gonzaga's Apparel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header" id="top">
    <div class="header-inner container">
        <a class="brand brand--header" href="#home" aria-label="Gonzaga's Apparel home">
            <img class="logo-placeholder logo-placeholder--header" src="assets/images/logo-placeholder.svg" 
            alt="Gonzaga’s Apparel logo placeholder">
        </a>
            <button class="menu-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
                <nav class="site-nav" id="site-nav" aria-label="Primary navigation"><?php foreach($navItems as $item): ?>
                    <a href="<?=e($item['href'])?>" class="nav-link<?= $item['label']==='HOME'?' is-active':'' ?>"><?=e($item['label'])?></a>
                    <?php endforeach; ?><?php if($user): ?><?php if(($user['role']??'')==='admin'): ?>
                        <a class="nav-link" href="admin/dashboard.php">ADMIN</a>
                        <?php else: ?><a class="nav-link" href="customer/profile.php">PROFILE</a>
                            <a class="nav-link" href="customer/orders.php">ORDERS</a>
                            <?php endif; ?>
                        <form class="nav-logout-form" action="logout.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                            <button class="nav-link nav-logout-btn" type="submit">LOG OUT</button>
                        </form>
                        <?php else: ?><a class="nav-link" href="login.php">LOGIN</a>
                            <?php endif; ?>
                        </nav>
                            <a class="bag-button" href="customer/cart.php" aria-label="Shopping bag"><?=icon('bag')?>
                            <span class="bag-count"><?=cart_count()?></span>
                        </a>
                    </div>
                </header>
<?php if($flash): ?><div class="site-flash <?=e($flash['type'])?>" role="status"><?=e($flash['message'])?></div><?php endif; ?>

    <main>

<section class="hero" id="home"><div class="hero-copy">
    <div class="section-kicker">CLOTHING THAT DEFINES YOU <span></span></div>
    <h1>STAY COMFORTABLE.<br>STAY CONFIDENT.</h1>
    <p class="hero-lede">Minimal design. Maximum impact.<br>Every piece is made to elevate<br>your everyday.</p>
<a class="button button--filled" href="#collection">SHOP COLLECTION <?=icon('arrow')?></a>
</div>
<div class="media-placeholder media-placeholder--hero">
    <img class="hero-svg" src="assets/images/hero-placeholder.png" alt="Hero image placeholder">
</div>
</section>

<section class="about section" id="about">
    <div class="about-logo-area">
        <div class="large-brand">
            <img class="logo-placeholder logo-placeholder--large" src="assets/images/logo2-placeholder.svg" alt="Gonzaga's Apparel logo placeholder">
        </div>
    </div>
            <div class="about-copy"><div class="section-kicker">ABOUT US <span></span></div>
            <h2>SIMPLE. MODERN. PURPOSEFUL.</h2>
            <p>Gonzaga's Apparel is a clothing brand built on minimalism, quality, and everyday wearability. Our goal is to create timeless pieces that fit your lifestyle and express who you are.</p>
            <a class="button button--outline" href="#contact">LEARN MORE <?=icon('arrow')?></a>
        </div>
        </section>
<section class="values section" id="why-us">
    <div class="section-heading">
        <div class="section-kicker">WHY CHOOSE US <span></span></div>
        <h2>MADE FOR QUALITY. MADE FOR YOU.</h2></div>
        <div class="values-grid container"><?php foreach($values as $value): ?><article class="value-card">
            <div class="line-icon"><?=icon($value['icon'])?></div>
            <h3><?=e($value['title'])?></h3>
            <p><?=e($value['copy'])?></p>
        </article><?php endforeach; ?>
    </div>
    </section>

<section class="collection section" id="collection">
    <div class="section-heading">
        <div class="section-kicker">FEATURED COLLECTION</div>
        <h2>OUR BESTSELLERS</h2></div>
        <div class="product-grid container"><?php foreach($products as $product): ?>
            <article class="product-card">
                <div class="product-image-wrap">
                    <img src="<?=e(product_image_src($product['image']))?>" alt="<?=e($product['name'])?>"></div>
                    <h3><?=e($product['name'])?></h3>
                    <p class="product-price"><?=money((float)$product['price'])?></p>
                    <form action="actions/cart_process.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id']?>">
                        <input type="hidden" name="action" value="add">
                        <button class="product-add" type="submit" <?=((int)$product['stock']<1?'disabled':'')?>><?=((int)$product['stock']<1?'OUT OF STOCK':'ADD TO BAG')?></button></form></article>
                        <?php endforeach; ?></div>
                        <div class="center-button">
                            <a class="button button--outline" href="collection.php">SHOP ALL <?=icon('arrow')?></a>
                            <a class="button button--outline" href="customer/cart.php">VIEW BAG <?=icon('arrow')?></a>
                        </div>
                        </section>

<section class="testimonials section" id="lookbook">
    <div class="section-heading">
        <div class="section-kicker">WHAT THEY SAY <span></span></div></div>
        <div class="testimonial-grid container"><?php foreach($testimonials as $testimonial): ?>
            <article class="testimonial-card"><div class="quote-mark">“</div>
            <div class="testimonial-body">
                <p><?=e($testimonial['quote'])?></p>
                <strong>— <?=e($testimonial['name'])?></strong></div></article>
                <?php endforeach; ?>
            </div>
            </section>

<section class="community" id="contact">
    <div class="community-media media-placeholder">
        <img src="assets/images/community-placeholder.png" alt="Community photo placeholder"></div>
        <div class="community-content container"><h2>BE PART OF THE GONZAGA’S APPAREL COMMUNITY</h2>
        <p>Get updates on new drops, exclusive offers, and more.</p>
        <form class="subscribe-form" action="actions/subscribe_process.php" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <label class="sr-only" for="email">Enter your email</label>
            <input id="email" name="email" type="email" placeholder="Enter your email" autocomplete="email" required>
            <button type="submit">SUBSCRIBE</button>
        </form>
        </div>
        </section>

</main>
<footer class="site-footer">
    <div class="footer-inner container">
        <div class="footer-brand">
            <a class="brand brand--footer" href="#home" aria-label="Gonzaga's Apparel home">
                <img class="logo-placeholder logo-placeholder--footer" src="assets/images/logo2-placeholder.svg" alt="Gonzaga’s Apparel footer logo placeholder">
            </a>
        </div>
        <div class="footer-column">
            <h3>QUICK LINKS</h3>
            <?php foreach($quickLinks as $link): ?>
                <a href="#<?=strtolower($link)==='contact'?'contact':strtolower($link)?>"><?=e($link)?></a>
                <?php endforeach; ?>
            </div>
            <div class="footer-column">
                <h3>CUSTOMER CARE</h3>
                <?php
                $careMap = [
                    'FAQS'               => 'customer/faqs.php',
                    'Shipping & Returns' => 'customer/shipping.php',
                    'Size Guide'         => 'customer/size-guide.php',
                    'Privacy Policy'     => 'customer/privacy.php',
                    'Terms & Conditions' => 'customer/terms.php',
                ];
                foreach($careMap as $label => $href): ?>
                    <a href="<?=e($href)?>"><?=e($label)?></a>
                <?php endforeach; ?>
                </div>
                <div class="footer-column footer-social">
                    <h3>FOLLOW US</h3>
                    <div class="socials">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg>
                        </a>
                        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.74a4.85 4.85 0 0 1-1.01-.05z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">© <?=date('Y')?> Gonzaga's Apparel. All Rights Reserved.
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
