<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Shipping &amp; Returns | Gonzaga's Apparel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="site-header" id="top">
    <div class="header-inner container">
        <a class="brand brand--header" href="../index.php" aria-label="Gonzaga's Apparel home">
            <img class="logo-placeholder logo-placeholder--header" src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel logo">
        </a>
        <nav class="site-nav" id="site-nav" aria-label="Primary navigation">
            <a href="../index.php" class="nav-link">HOME</a>
            <a href="../index.php#about" class="nav-link">ABOUT</a>
            <a href="../collection.php" class="nav-link">COLLECTION</a>
            <a href="../index.php#contact" class="nav-link">CONTACT</a>
            <?php if($user): ?>
                <?php if(($user['role']??'') === 'admin'): ?>
                    <a class="nav-link" href="../admin/dashboard.php">ADMIN</a>
                <?php else: ?>
                    <a class="nav-link" href="profile.php">PROFILE</a>
                    <a class="nav-link" href="orders.php">ORDERS</a>
                <?php endif; ?>
                <form class="nav-logout-form" action="../logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                    <button class="nav-link nav-logout-btn" type="submit">LOG OUT</button>
                </form>
            <?php else: ?>
                <a class="nav-link" href="../login.php">LOGIN</a>
            <?php endif; ?>
        </nav>
        <a class="bag-button" href="cart.php" aria-label="Shopping bag"><?=icon('bag')?>
            <span class="bag-count"><?=cart_count()?></span>
        </a>
    </div>
</header>

<main class="shop-shell narrow">
    <div class="section-kicker">CUSTOMER CARE <span></span></div>
    <h1>SHIPPING &amp; RETURNS</h1>

    <div class="care-page">
        <h2>Shipping Information</h2>
        <p>We ship nationwide across the Philippines. All orders are processed within 1–2 business days.</p>

        <h2>Delivery Times</h2>
        <ul>
            <li><strong>Metro Manila:</strong> 2–4 business days</li>
            <li><strong>Provincial (Luzon):</strong> 4–6 business days</li>
            <li><strong>Visayas &amp; Mindanao:</strong> 5–8 business days</li>
        </ul>

        <h2>Shipping Fees</h2>
        <p>Flat rate of ₱100 for all orders under ₱1,500. Free shipping on orders ₱1,500 and above.</p>

        <h2>Return Policy</h2>
        <p>We accept returns within 7 days of delivery for items that are unworn, unwashed, and in original condition with tags attached.</p>

        <h2>How to Return</h2>
        <ol>
            <li>Email us at support@gonzagasapparel.com with your order number and reason for return.</li>
            <li>Wait for our confirmation and return instructions.</li>
            <li>Ship the item back to our address using any courier.</li>
            <li>Once received and inspected, we'll process your refund or exchange within 3–5 business days.</li>
        </ol>

        <h2>Non-Returnable Items</h2>
        <p>Sale items, accessories, and items marked as final sale are not eligible for return or exchange.</p>
    </div>
    <a class="button button--outline" href="../index.php">&larr; Back to Home</a>
</main>

<footer class="site-footer">
    <div class="footer-inner container">
        <div class="footer-brand">
            <a class="brand brand--footer" href="../index.php" aria-label="Gonzaga's Apparel home">
                <img class="logo-placeholder logo-placeholder--footer" src="../assets/images/logo2-placeholder.svg" alt="Gonzaga's Apparel footer logo">
            </a>
        </div>
        <div class="footer-column">
            <h3>QUICK LINKS</h3>
            <a href="../index.php">Home</a>
            <a href="../index.php#about">About</a>
            <a href="../collection.php">Collection</a>
            <a href="../index.php#contact">Contact</a>
        </div>
        <div class="footer-column">
            <h3>CUSTOMER CARE</h3>
            <a href="faqs.php">FAQs</a>
            <a href="shipping.php">Shipping &amp; Returns</a>
            <a href="size-guide.php">Size Guide</a>
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms &amp; Conditions</a>
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
    <div class="footer-bottom">© <?=date('Y')?> Gonzaga's Apparel. All Rights Reserved.</div>
</footer>
<script src="../js/script.js"></script>
</body>
</html>
