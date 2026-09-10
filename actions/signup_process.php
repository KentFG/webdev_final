<?php
declare(strict_types=1);

require_once __DIR__ . '/../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('signup.php'));
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid security token. Please try again.');
    header('Location: ' . url('signup.php'));
    exit;
}

$name = clean_text($_POST['name'] ?? '');
$email = strtolower(clean_text($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');
$errors = validate_signup($name, $email, $password, $confirm);

if (!$errors) {
    $check = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        $errors[] = 'An account with that email already exists.';
    }
}

if ($errors) {
    $_SESSION['old_signup'] = ['name' => $name, 'email' => $email];
    $_SESSION['form_errors'] = $errors;
    header('Location: ' . url('signup.php'));
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, \'customer\', \'active\')');
$stmt->execute([$name, $email, $hash]);

set_flash('success', 'Your customer account has been created. Please log in.');
header('Location: ' . url('login.php'));
exit;
