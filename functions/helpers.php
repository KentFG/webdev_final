<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validation.php';

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_PATH . ($path !== '' && $path[0] !== '/' ? '/' . $path : $path); }
function icon(string $name): string {
    $allowed=['arrow','bag','quality','comfort','style','care'];
    return in_array($name,$allowed,true) ? '<img class="placeholder-icon placeholder-icon--'.e($name).'" src="assets/icons/'.e($name).'-placeholder.svg" alt="" aria-hidden="true">' : '';
}
function cart_count(): int { return array_sum(array_map('intval', $_SESSION['cart'] ?? [])); }
function money(float $amount): string { return '₱' . number_format($amount, 2); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function product_image_src(?string $image, string $basePrefix = ''): string {
    $img = trim((string)$image);
    if ($img === '') {
        return $basePrefix . 'assets/images/black-tshirt-placeholder.svg';
    }
    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
        return $img;
    }
    if (str_starts_with($img, 'assets/')) {
        return $basePrefix . $img;
    }
    if (str_starts_with($img, '/')) {
        return $img;
    }
    return $basePrefix . 'assets/images/' . $img;
}
