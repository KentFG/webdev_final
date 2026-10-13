<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * In-memory & file-cached data store.
 * Eliminates the need for a database while persisting changes across requests.
 */
class DataStore
{
    private string $dataFile;
    private array $data = [];
    private RequestStack $requestStack;

    public function __construct(string $projectDir, RequestStack $requestStack)
    {
        $this->dataFile = $projectDir . '/var/data/store.json';
        $this->requestStack = $requestStack;
        $this->load();
    }

    private function getSession(): ?SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function load(): void
    {
        if (file_exists($this->dataFile)) {
            $content = file_get_contents($this->dataFile);
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $this->data = $decoded;
                return;
            }
        }

        $this->data = $this->getDefaultData();
        $this->persist();
    }

    private function persist(): void
    {
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->dataFile, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function getDefaultData(): array
    {
        return [
            'categories' => [
                1 => ['id' => 1, 'name' => 'T-Shirts', 'slug' => 't-shirts', 'description' => 'Essential everyday tops.', 'is_active' => 1],
                2 => ['id' => 2, 'name' => 'Hoodies', 'slug' => 'hoodies', 'description' => 'Comfort-first layers.', 'is_active' => 1],
                3 => ['id' => 3, 'name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Caps, bags, and everyday carry pieces.', 'is_active' => 1],
            ],
            'products' => [
                1 => [
                    'id' => 1,
                    'category_id' => 1,
                    'category_name' => 'T-Shirts',
                    'name' => 'ESSENTIAL BLACK T-SHIRT',
                    'description' => 'Minimal everyday essential.',
                    'price' => 899.00,
                    'stock' => 25,
                    'image' => 'black-tshirt-placeholder.svg',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                2 => [
                    'id' => 2,
                    'category_id' => 1,
                    'category_name' => 'T-Shirts',
                    'name' => 'ESSENTIAL WHITE T-SHIRT',
                    'description' => 'Clean, versatile everyday essential.',
                    'price' => 899.00,
                    'stock' => 25,
                    'image' => 'white-tshirt-placeholder.svg',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                3 => [
                    'id' => 3,
                    'category_id' => 2,
                    'category_name' => 'Hoodies',
                    'name' => 'MINIMAL HOODIE',
                    'description' => 'Comfort-first minimal hoodie.',
                    'price' => 1599.00,
                    'stock' => 15,
                    'image' => 'hoodie-placeholder.svg',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                4 => [
                    'id' => 4,
                    'category_id' => 3,
                    'category_name' => 'Accessories',
                    'name' => 'CLASSIC CAP',
                    'description' => 'Timeless cap for everyday styling.',
                    'price' => 699.00,
                    'stock' => 20,
                    'image' => 'cap-placeholder.svg',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                5 => [
                    'id' => 5,
                    'category_id' => 3,
                    'category_name' => 'Accessories',
                    'name' => 'LOGO TOTE BAG',
                    'description' => 'Simple tote for everyday carry.',
                    'price' => 499.00,
                    'stock' => 30,
                    'image' => 'tote-placeholder.svg',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ],
            'users' => [
                1 => [
                    'id' => 1,
                    'name' => 'Gonzaga Admin',
                    'email' => 'admin@gonzagas.local',
                    'password_hash' => password_hash('Admin@123!', PASSWORD_BCRYPT),
                    'role' => 'admin',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Customer Demo',
                    'email' => 'customer@gonzagas.local',
                    'password_hash' => password_hash('Customer@123!', PASSWORD_BCRYPT),
                    'role' => 'customer',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ],
            'profiles' => [
                1 => [
                    'user_id' => 1,
                    'phone' => '0917 123 4567',
                    'address_line1' => '123 Main Street',
                    'address_line2' => 'Suite 100',
                    'city' => 'Makati City',
                    'province' => 'Metro Manila',
                    'postal_code' => '1200',
                    'country' => 'Philippines',
                ],
                2 => [
                    'user_id' => 2,
                    'phone' => '0918 987 6543',
                    'address_line1' => '456 Bonifacio Blvd',
                    'address_line2' => 'Tower 2 Unit 4B',
                    'city' => 'Taguig',
                    'province' => 'Metro Manila',
                    'postal_code' => '1630',
                    'country' => 'Philippines',
                ],
            ],
            'orders' => [
                1001 => [
                    'id' => 1001,
                    'user_id' => 2,
                    'account_name' => 'Customer Demo',
                    'email' => 'customer@gonzagas.local',
                    'total_amount' => 1798.00,
                    'subtotal_amount' => 1798.00,
                    'shipping_fee' => 0.00,
                    'status' => 'processing',
                    'payment_method' => 'cod',
                    'payment_receipt' => null,
                    'payment_reference' => '',
                    'payment_status' => 'pending',
                    'driver_confirmation' => 'unconfirmed',
                    'driver_name' => '',
                    'driver_notes' => '',
                    'driver_confirmed_at' => null,
                    'shipping_name' => 'Customer Demo',
                    'shipping_phone' => '0918 987 6543',
                    'shipping_address1' => '456 Bonifacio Blvd',
                    'shipping_address2' => 'Tower 2 Unit 4B',
                    'shipping_city' => 'Taguig',
                    'shipping_province' => 'Metro Manila',
                    'shipping_postal_code' => '1630',
                    'shipping_country' => 'Philippines',
                    'notes' => 'Please ring the doorbell upon arrival.',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'items' => [
                        [
                            'id' => 1,
                            'order_id' => 1001,
                            'product_id' => 1,
                            'product_name' => 'ESSENTIAL BLACK T-SHIRT',
                            'image' => 'black-tshirt-placeholder.svg',
                            'unit_price' => 899.00,
                            'quantity' => 2,
                            'subtotal' => 1798.00,
                        ],
                    ],
                ],
            ],
            'subscribers' => [
                'hello@gonzagas.local',
            ],
        ];
    }

    // Category methods
    public function getCategories(): array
    {
        return $this->data['categories'] ?? [];
    }

    public function getCategory(int $id): ?array
    {
        return $this->data['categories'][$id] ?? null;
    }

    public function saveCategory(array $category): int
    {
        if (empty($category['id'])) {
            $ids = array_keys($this->data['categories'] ?? []);
            $id = $ids ? max($ids) + 1 : 1;
            $category['id'] = $id;
        } else {
            $id = (int) $category['id'];
        }

        if (empty($category['slug'])) {
            $category['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category['name'] ?? 'category')));
        }
        $category['is_active'] = isset($category['is_active']) ? (int)$category['is_active'] : 1;

        $this->data['categories'][$id] = $category;
        $this->persist();
        return $id;
    }

    // Product methods
    public function getProducts(array $filters = []): array
    {
        $products = array_values($this->data['products'] ?? []);

        if (isset($filters['active_only']) && $filters['active_only']) {
            $products = array_filter($products, fn($p) => !empty($p['is_active']));
        }

        if (!empty($filters['category_id'])) {
            $catId = (int)$filters['category_id'];
            $products = array_filter($products, fn($p) => (int)($p['category_id'] ?? 0) === $catId);
        }

        if (!empty($filters['search'])) {
            $q = mb_strtolower(trim($filters['search']));
            $products = array_filter($products, function($p) use ($q) {
                return str_contains(mb_strtolower($p['name'] ?? ''), $q) || str_contains(mb_strtolower($p['description'] ?? ''), $q);
            });
        }

        return array_values($products);
    }

    public function getProduct(int $id): ?array
    {
        return $this->data['products'][$id] ?? null;
    }

    public function saveProduct(array $product): int
    {
        if (empty($product['id'])) {
            $ids = array_keys($this->data['products'] ?? []);
            $id = $ids ? max($ids) + 1 : 1;
            $product['id'] = $id;
            $product['created_at'] = date('Y-m-d H:i:s');
        } else {
            $id = (int) $product['id'];
        }

        $catId = (int) ($product['category_id'] ?? 0);
        $category = $this->getCategory($catId);
        $product['category_name'] = $category ? $category['name'] : 'Uncategorized';
        $product['price'] = (float) ($product['price'] ?? 0.0);
        $product['stock'] = (int) ($product['stock'] ?? 0);
        $product['is_active'] = isset($product['is_active']) ? (int) $product['is_active'] : 1;
        $product['image'] = !empty($product['image']) ? $product['image'] : 'black-tshirt-placeholder.svg';

        $this->data['products'][$id] = $product;
        $this->persist();
        return $id;
    }

    public function deleteProduct(int $id): void
    {
        unset($this->data['products'][$id]);
        $this->persist();
    }

    // User & Profile methods
    public function getUsers(): array
    {
        return array_values($this->data['users'] ?? []);
    }

    public function getUser(int $id): ?array
    {
        return $this->data['users'][$id] ?? null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $normalized = mb_strtolower(trim($email));
        foreach ($this->data['users'] ?? [] as $user) {
            if (mb_strtolower(trim($user['email'])) === $normalized) {
                return $user;
            }
        }
        return null;
    }

    public function saveUser(array $user): int
    {
        if (empty($user['id'])) {
            $ids = array_keys($this->data['users'] ?? []);
            $id = $ids ? max($ids) + 1 : 1;
            $user['id'] = $id;
            $user['created_at'] = date('Y-m-d H:i:s');
        } else {
            $id = (int) $user['id'];
        }

        $this->data['users'][$id] = $user;
        $this->persist();
        return $id;
    }

    public function getProfile(int $userId): array
    {
        return $this->data['profiles'][$userId] ?? [
            'user_id' => $userId,
            'phone' => '',
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'province' => '',
            'postal_code' => '',
            'country' => 'Philippines',
        ];
    }

    public function saveProfile(int $userId, array $profileData): void
    {
        $profileData['user_id'] = $userId;
        $this->data['profiles'][$userId] = array_merge($this->getProfile($userId), $profileData);
        $this->persist();
    }

    // Order methods
    public function getOrders(array $filters = []): array
    {
        $orders = array_values($this->data['orders'] ?? []);

        if (!empty($filters['user_id'])) {
            $userId = (int) $filters['user_id'];
            $orders = array_filter($orders, fn($o) => (int)($o['user_id'] ?? 0) === $userId);
        }

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            $orders = array_filter($orders, fn($o) => ($o['status'] ?? '') === $status);
        }

        // Sort descending by id/date
        usort($orders, fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

        return array_values($orders);
    }

    public function getOrder(int $id): ?array
    {
        return $this->data['orders'][$id] ?? null;
    }

    public function saveOrder(array $orderData, array $items): int
    {
        if (empty($orderData['id'])) {
            $ids = array_keys($this->data['orders'] ?? []);
            $id = $ids ? max($ids) + 1 : 1001;
            $orderData['id'] = $id;
            $orderData['created_at'] = date('Y-m-d H:i:s');
        } else {
            $id = (int) $orderData['id'];
        }

        // Decrement product stocks
        foreach ($items as $item) {
            $pId = (int) ($item['product_id'] ?? 0);
            if (isset($this->data['products'][$pId])) {
                $qty = (int) ($item['quantity'] ?? 1);
                $this->data['products'][$pId]['stock'] = max(0, (int)$this->data['products'][$pId]['stock'] - $qty);
            }
        }

        $orderData['items'] = $items;
        $this->data['orders'][$id] = $orderData;
        $this->persist();
        return $id;
    }

    public function updateOrder(int $id, array $updates): void
    {
        if (isset($this->data['orders'][$id])) {
            $this->data['orders'][$id] = array_merge($this->data['orders'][$id], $updates);
            $this->persist();
        }
    }

    // Subscriber methods
    public function addSubscriber(string $email): bool
    {
        $clean = mb_strtolower(trim($email));
        if (!in_array($clean, $this->data['subscribers'] ?? [], true)) {
            $this->data['subscribers'][] = $clean;
            $this->persist();
            return true;
        }
        return false;
    }

    public function getStats(): array
    {
        $orders = $this->data['orders'] ?? [];
        $users = $this->data['users'] ?? [];
        $products = $this->data['products'] ?? [];

        $customerCount = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'customer'));
        $orderCount = count($orders);
        $revenue = 0.0;
        foreach ($orders as $order) {
            if (($order['status'] ?? '') !== 'cancelled') {
                $revenue += (float) ($order['total_amount'] ?? 0);
            }
        }

        return [
            'userCount' => $customerCount,
            'orderCount' => $orderCount,
            'revenue' => $revenue,
            'productCount' => count($products),
        ];
    }
}
