<?php
declare(strict_types=1);

require_once __DIR__ . '/../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('login.php'));
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid security token. Please try again.');
    header('Location: ' . url('login.php'));
    exit;
}

$email = clean_text($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$errors = validate_login($email, $password);

if ($errors) {
    $_SESSION['old_login'] = ['email' => $email];
    $_SESSION['form_errors'] = $errors;
    header('Location: ' . url('login.php'));
    exit;
}

$stmt = db()->prepare('SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
    $_SESSION['old_login'] = ['email' => $email];
    $_SESSION['form_errors'] = ['Invalid email or password.'];
    header('Location: ' . url('login.php'));
    exit;
}

if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $rehash = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $rehash->execute([$newHash, $user['id']]);
}

login_user($user);
set_flash('success', 'Welcome back, ' . $user['name'] . '!');
redirect_by_role();
