<?php
declare(strict_types=1);

require_once __DIR__ . '/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    logout_user();
    session_start();
    set_flash('success', 'You have been logged out safely.');
}

header('Location: ' . url('index.php'));
exit;
