<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ? AND status = \'active\' LIMIT 1');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;

    if (!$user) {
        logout_user();
    }

    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['last_activity'] = time();
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function require_guest(): void
{
    if (is_logged_in()) {
        redirect_by_role();
    }
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }
}

function require_role(string $role): void
{
    require_login();

    if (($_SESSION['role'] ?? '') !== $role) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect_by_role();
    }
}

function redirect_by_role(): void
{
    $role = $_SESSION['role'] ?? null;

    if ($role === 'admin') {
        header('Location: ' . BASE_PATH . '/admin/dashboard.php');
        exit;
    }

    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
