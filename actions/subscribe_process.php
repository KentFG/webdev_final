<?php
declare(strict_types=1);

require_once __DIR__ . '/../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request.');
    header('Location: ' . url('index.php#contact'));
    exit;
}

$email = strtolower(clean_text($_POST['email'] ?? ''));

if (!valid_email($email)) {
    set_flash('error', 'Please enter a valid email address.');
    header('Location: ' . url('index.php#contact'));
    exit;
}

$stmt = db()->prepare('INSERT INTO subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE email=email');
$stmt->execute([$email]);

set_flash('success', 'Thank you. You are on the list.');
header('Location: ' . url('index.php#contact'));
exit;