<?php
declare(strict_types=1);
require_once __DIR__ . '/functions/helpers.php';
require_guest();
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_login'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_login']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Login | Gonzaga's Apparel</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <a class="auth-logo" href="index.php"><img src="assets/images/logo2-placeholder.svg" alt="Gonzaga's Apparel logo placeholder"></a>
        <section class="auth-card">
            <div class="section-kicker">WELCOME BACK <span></span></div>
            <h1>LOGIN</h1>
            <p class="auth-intro">Access your Gonzaga’s Apparel account.</p>
            <?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <form action="actions/login_process.php" method="post" class="auth-form" novalidate autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label for="email">EMAIL</label>
                <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="new-email" required>
                <label for="password">PASSWORD</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                <button class="button button--filled" type="submit">LOGIN</button>
            </form>
            <p class="auth-switch">New customer? <a href="signup.php">Create an account</a></p>
            <a class="auth-back" href="index.php">← Back to homepage</a>
        </section>
    </main>
</body>
</html>
