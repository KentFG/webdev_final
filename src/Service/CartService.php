<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private DataStore $dataStore;
    private RequestStack $requestStack;

    public function __construct(DataStore $dataStore, RequestStack $requestStack)
    {
        $this->dataStore = $dataStore;
        $this->requestStack = $requestStack;
    }

    private function getSession(): ?SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getCart(): array
    {
        $session = $this->getSession();
        return $session ? $session->get('cart', []) : [];
    }

    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_map('intval', $cart));
    }

    public function addItem(int $productId, int $qty = 1): bool
    {
        $product = $this->dataStore->getProduct($productId);
        if (!$product || empty($product['is_active']) || (int)$product['stock'] < 1) {
            return false;
        }

        $session = $this->getSession();
        if (!$session) {
            return false;
        }

        $cart = $this->getCart();
        $current = (int) ($cart[$productId] ?? 0);
        $newQty = min((int)$product['stock'], $current + max(1, $qty));
        $cart[$productId] = $newQty;
        $session->set('cart', $cart);

        return true;
    }

    public function removeItem(int $productId): void
    {
        $session = $this->getSession();
        if (!$session) {
            return;
        }

        $cart = $this->getCart();
        unset($cart[$productId]);
        $session->set('cart', $cart);
    }

    public function updateItem(int $productId, int $qty): void
    {
        $session = $this->getSession();
        if (!$session) {
            return;
        }

        $cart = $this->getCart();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $product = $this->dataStore->getProduct($productId);
            if ($product && !empty($product['is_active'])) {
                $cart[$productId] = min((int)$product['stock'], $qty);
            }
        }
        $session->set('cart', $cart);
    }

    public function clearCart(): void
    {
        $session = $this->getSession();
        if ($session) {
            $session->remove('cart');
        }
    }

    public function getItemsAndTotals(): array
    {
        $cart = $this->getCart();
        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $qty) {
            $product = $this->dataStore->getProduct((int) $productId);
            if (!$product || empty($product['is_active'])) {
                continue;
            }

            $stock = (int) $product['stock'];
            $actualQty = min((int) $qty, $stock);
            if ($actualQty < 1) {
                continue;
            }

            $price = (float) $product['price'];
            $lineSubtotal = $price * $actualQty;
            $subtotal += $lineSubtotal;

            $items[] = [
                'id' => $product['id'],
                'product_id' => $product['id'],
                'name' => $product['name'],
                'price' => $price,
                'stock' => $stock,
                'image' => $product['image'] ?? 'black-tshirt-placeholder.svg',
                'qty' => $actualQty,
                'subtotal' => $lineSubtotal,
            ];
        }

        $shipping = ($subtotal >= 2500 || $subtotal === 0.0) ? 0.0 : 150.0;
        $total = $subtotal + $shipping;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'count' => array_sum(array_column($items, 'qty')),
        ];
    }
}
