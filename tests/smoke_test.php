<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use App\Service\DataStore;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$kernel = new Kernel('dev', false);
$kernel->boot();

$urls = [
    '/' => 200,
    '/collection' => 200,
    '/collection?q=t-shirt' => 200,
    '/cart' => 200,
    '/login' => 200,
    '/signup' => 200,
    '/faqs' => 200,
    '/shipping' => 200,
    '/size-guide' => 200,
    '/privacy' => 200,
    '/terms' => 200,
    '/admin' => 302, // Unauthenticated admin redirects to login
    '/checkout' => 302, // Unauthenticated checkout redirects to login
    '/customer/orders' => 302, // Unauthenticated orders redirects to login
];

$passed = 0;
foreach ($urls as $url => $expectedCode) {
    $request = Request::create($url);
    $session = new Session(new MockArraySessionStorage());
    $request->setSession($session);

    $response = $kernel->handle($request);
    $code = $response->getStatusCode();
    if ($code === $expectedCode) {
        echo "[PASS] {$url} => {$code}" . PHP_EOL;
        $passed++;
    } else {
        echo "[FAIL] {$url} => {$code} (expected {$expectedCode})" . PHP_EOL;
    }
}

// Test Authenticated Admin Views (Dashboard, Orders, Product Form, Category Form)
$adminUrls = [
    '/admin/dashboard',
    '/admin/orders',
    '/admin/product/new',
    '/admin/category/new',
];

foreach ($adminUrls as $adminUrl) {
    $request = Request::create($adminUrl);
    $session = new Session(new MockArraySessionStorage());
    $session->set('user_id', 1);
    $session->set('role', 'admin');
    $session->set('name', 'Gonzaga Admin');
    $request->setSession($session);

    $response = $kernel->handle($request);
    $code = $response->getStatusCode();
    if ($code === 200) {
        echo "[PASS] (Authenticated Admin) {$adminUrl} => {$code}" . PHP_EOL;
        $passed++;
    } else {
        echo "[FAIL] (Authenticated Admin) {$adminUrl} => {$code} (expected 200)" . PHP_EOL;
    }
}

// Test Direct DataStore
$requestStack = new RequestStack();
$dataStore = new DataStore(dirname(__DIR__), $requestStack);
$categories = $dataStore->getCategories();
$products = $dataStore->getProducts();

if (count($categories) >= 3 && count($products) >= 5) {
    echo "[PASS] DataStore seeded with " . count($categories) . " categories and " . count($products) . " products." . PHP_EOL;
    $passed++;
} else {
    echo "[FAIL] DataStore seeding mismatch." . PHP_EOL;
}

$adminUser = $dataStore->getUserByEmail('admin@gonzagas.local');
if ($adminUser && ($adminUser['role'] ?? '') === 'admin') {
    echo "[PASS] Admin user exists with correct role." . PHP_EOL;
    $passed++;
} else {
    echo "[FAIL] Admin user missing." . PHP_EOL;
}

echo PHP_EOL . "Total passed: {$passed} / " . (count($urls) + count($adminUrls) + 2) . PHP_EOL;
