<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('customer');

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    set_flash('error', 'Your bag is empty.');
    redirect('customer/cart.php');
}

$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = db()->prepare("SELECT p.id, p.name, p.price, p.stock, p.image FROM products p WHERE p.is_active = 1 AND p.id IN ($placeholders)");
$stmt->execute(array_map('intval', $ids));
$products = [];
foreach ($stmt->fetchAll() as $row) {
    $products[(int)$row['id']] = $row;
}

$subtotal = 0;
$items = [];
foreach ($cart as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;
    if (isset($products[$id]) && $qty > 0) {
        $qty = min($qty, (int)$products[$id]['stock']);
        $sub = (float)$products[$id]['price'] * $qty;
        $subtotal += $sub;
        $items[] = [
            'id' => $id,
            'name' => $products[$id]['name'],
            'qty' => $qty,
            'price' => (float)$products[$id]['price'],
            'subtotal' => $sub
        ];
    }
}

$shipping = $subtotal >= 2500 ? 0 : 150;
$total = $subtotal + $shipping;

$stmt = db()->prepare('SELECT u.name, p.phone, p.address_line1, p.address_line2, p.city, p.province, p.postal_code, p.country FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.id = ?');
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch() ?: [];

$errors = $_SESSION['checkout_errors'] ?? [];
$old = $_SESSION['checkout_old'] ?? $profile;
$selectedPayment = $old['payment_method'] ?? 'cod';
unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Checkout | Gonzaga's Apparel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body class="shop-page">
<header class="mini-header">
    <a href="../index.php"><img src="../assets/images/logo-placeholder.svg" alt="Gonzaga's Apparel"></a>
    <nav>
        <a href="cart.php">BAG (<?=cart_count()?>)</a>
        <a href="profile.php">PROFILE</a>
        <form action="../logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <button type="submit">LOG OUT</button>
        </form>
    </nav>
</header>

<main class="shop-shell checkout-shell">
    <div class="section-kicker">CHECKOUT <span></span></div>
    <h1>COMPLETE YOUR ORDER</h1>

    <?php if ($errors): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?=e($error)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form class="auth-form" action="../actions/checkout_process.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">

            <h2>SHIPPING DETAILS</h2>
            <label>FULL NAME</label>
            <input name="name" value="<?=e($old['name'] ?? '')?>" required>

            <label>PHONE</label>
            <input name="phone" value="<?=e($old['phone'] ?? '')?>" required placeholder="09xxxxxxxxx">

            <label>ADDRESS LINE 1</label>
            <input name="address_line1" value="<?=e($old['address_line1'] ?? '')?>" required>

            <label>ADDRESS LINE 2 (OPTIONAL)</label>
            <input name="address_line2" value="<?=e($old['address_line2'] ?? '')?>">

            <div class="form-grid">
                <div>
                    <label>CITY</label>
                    <input name="city" value="<?=e($old['city'] ?? '')?>" required>
                </div>
                <div>
                    <label>PROVINCE</label>
                    <input name="province" value="<?=e($old['province'] ?? '')?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div>
                    <label>POSTAL CODE</label>
                    <input name="postal_code" value="<?=e($old['postal_code'] ?? '')?>" required>
                </div>
                <div>
                    <label>COUNTRY</label>
                    <input name="country" value="<?=e($old['country'] ?? 'Philippines')?>" required>
                </div>
            </div>

            <h2>PAYMENT METHOD</h2>
            <div class="payment-method-selector">
                <label class="payment-choice-label <?=($selectedPayment==='cod'?'is-selected-cod':'')?>" id="label_cod">
                    <input type="radio" name="payment_method" value="cod" <?=($selectedPayment==='cod'?'checked':'')?> onchange="togglePaymentSection('cod')">
                    <span>?? Cash on Delivery</span>
                </label>
                <label class="payment-choice-label <?=($selectedPayment==='gcash'?'is-selected':'')?>" id="label_gcash">
                    <input type="radio" name="payment_method" value="gcash" <?=($selectedPayment==='gcash'?'checked':'')?> onchange="togglePaymentSection('gcash')">
                    <span><img src="../assets/icons/gcash.svg" alt="GCash" style="height:18px; width:18px; display:inline-block; vertical-align:middle; margin-right:4px;"> GCash (E-Wallet)</span>
                </label>
            </div>

            <!-- COD Details -->
            <div id="cod_details_box" class="payment-option-card <?=($selectedPayment==='cod'?'is-active':'')?>" style="<?=($selectedPayment==='cod'?'':'display:none;')?>">
                <div class="cod-badge-notice">
                    <strong>?? Cash on Delivery (COD)</strong><br>
                    Please prepare the exact cash amount of <strong><?=money($total)?></strong> upon delivery.<br>
                    <em>Note: Your delivery driver will confirm and acknowledge receipt of your payment upon handoff.</em>
                </div>
            </div>

            <!-- GCash Details -->
            <div id="gcash_details_box" class="payment-option-card <?=($selectedPayment==='gcash'?'is-active':'')?>" style="<?=($selectedPayment==='gcash'?'':'display:none;')?>">
                <div class="gcash-replica-header">
                    <div style="display:inline-flex; align-items:center; gap:8px;">
                        <img src="../assets/icons/gcash.svg" alt="GCash" class="gcash-replica-logo">
                        <strong style="font-size:18px; font-weight:800; color:#005ce6; letter-spacing:-0.2px;">GCash</strong>
                    </div>
                    <span style="font-size:11px; font-weight:700; color:#005ce6; letter-spacing:0.04em; margin-left:auto;">OFFICIAL MERCHANT ACCOUNT</span>
                </div>

                <div class="gcash-account-box">
                    <div>Merchant Account Name: <strong>GONZAGA'S APPAREL</strong></div>
                    <div style="margin-top:4px;">GCash Mobile Number: <strong>0917 123 4567</strong></div>
                    <div style="margin-top:4px;">Exact Amount to Pay: <strong style="font-size:14px;"><?=money($total)?></strong></div>
                </div>

                <label>UPLOAD CONFIRMATION RECEIPT PICTURE <span style="color:#d32f2f;">*</span></label>
                <input type="file" name="payment_receipt" id="payment_receipt_input" accept="image/*" onchange="previewReceipt(event)">
                <small style="display:block; color:#666; font-size:10px; margin-top:4px;">Please upload a screenshot or photo of your confirmed GCash transaction receipt (JPG, PNG, WEBP).</small>
                
                <div id="receipt_preview_box" class="receipt-preview-container">
                    <img id="receipt_preview_img" src="#" alt="Receipt Preview">
                    <span style="display:block; font-size:10px; color:#2e7d32; margin-top:4px;">? Receipt selected</span>
                </div>

                <label style="margin-top:12px;">GCASH REFERENCE NO. (OPTIONAL)</label>
                <input type="text" name="payment_reference" value="<?=e($old['payment_reference'] ?? '')?>" placeholder="e.g. 1002 9384 1928" maxlength="50">
            </div>

            <label style="margin-top:16px;">ORDER NOTES (OPTIONAL)</label>
            <textarea name="notes" rows="4" maxlength="500" placeholder="Delivery instructions, gate code, landmarks..."><?=e($old['notes'] ?? '')?></textarea>

            <button class="button button--filled" type="submit" style="margin-top:16px; width:100%;">CONFIRM ORDER · <?=money($total)?></button>
        </form>

        <aside class="checkout-summary">
            <h2>ORDER SUMMARY</h2>
            <?php foreach ($items as $item): ?>
                <div class="summary-row">
                    <span><?=e($item['name'])?> × <?=intval($item['qty'])?></span>
                    <strong><?=money($item['subtotal'])?></strong>
                </div>
            <?php endforeach; ?>
            <div class="summary-row">
                <span>Subtotal</span>
                <strong><?=money($subtotal)?></strong>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <strong><?=$shipping === 0 ? 'FREE' : money($shipping)?></strong>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <strong><?=money($total)?></strong>
            </div>
            <p class="checkout-note">Free shipping on orders of ?2,500 or more.</p>
        </aside>
    </div>
</main>

<script>
function togglePaymentSection(method) {
    const codBox = document.getElementById('cod_details_box');
    const gcashBox = document.getElementById('gcash_details_box');
    const labelCod = document.getElementById('label_cod');
    const labelGcash = document.getElementById('label_gcash');
    const receiptInput = document.getElementById('payment_receipt_input');

    if (method === 'gcash') {
        codBox.style.display = 'none';
        codBox.classList.remove('is-active');
        gcashBox.style.display = 'block';
        gcashBox.classList.add('is-active');
        labelGcash.classList.add('is-selected');
        labelCod.classList.remove('is-selected-cod');
        if (receiptInput) receiptInput.required = true;
    } else {
        gcashBox.style.display = 'none';
        gcashBox.classList.remove('is-active');
        codBox.style.display = 'block';
        codBox.classList.add('is-active');
        labelCod.classList.add('is-selected-cod');
        labelGcash.classList.remove('is-selected');
        if (receiptInput) receiptInput.required = false;
    }
}

function previewReceipt(event) {
    const file = event.target.files[0];
    const previewBox = document.getElementById('receipt_preview_box');
    const previewImg = document.getElementById('receipt_preview_img');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewBox.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewBox.style.display = 'none';
    }
}
</script>
</body>
</html>
