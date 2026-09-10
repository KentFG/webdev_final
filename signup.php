<?php
declare(strict_types=1);
require_once __DIR__ . '/functions/helpers.php';
require_guest();
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_signup'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_signup']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Sign Up | Gonzaga's Apparel</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <a class="auth-logo" href="index.php"><img src="assets/images/logo2-placeholder.svg" alt="Gonzaga's Apparel logo placeholder"></a>
        <section class="auth-card">
            <div class="section-kicker">NEW CUSTOMER <span></span></div>
            <h1>SIGN UP</h1>
            <p class="auth-intro">Create a customer account to shop and manage your orders.</p>
            <?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <form action="actions/signup_process.php" method="post" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label for="name">FULL NAME</label>
                <input id="name" name="name" type="text" value="<?= e($old['name'] ?? '') ?>" autocomplete="name" required>
                <label for="email">EMAIL</label>
                <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required>
                <label for="password">PASSWORD</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" pattern="(?=.*[^a-zA-Z0-9]).{8,}" title="At least 8 characters with at least 1 special character" required aria-describedby="password-help">
                <small id="password-help">At least 8 characters and at least 1 special character.</small>
                <label for="confirm_password">CONFIRM PASSWORD</label>
                <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="8" pattern="(?=.*[^a-zA-Z0-9]).{8,}" title="At least 8 characters with at least 1 special character" required>
                <button class="button button--filled" type="submit">CREATE ACCOUNT</button>
            </form>
            <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
            <a class="auth-back" href="index.php">← Back to homepage</a>
        </section>
    </main>
</body>
</html>
