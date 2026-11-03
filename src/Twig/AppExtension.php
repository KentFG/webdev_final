<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\AuthService;
use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private AuthService $authService;
    private CartService $cartService;

    public function __construct(AuthService $authService, CartService $cartService)
    {
        $this->authService = $authService;
        $this->cartService = $cartService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', [$this, 'renderIcon'], ['is_safe' => ['html']]),
            new TwigFunction('cart_count', [$this, 'getCartCount']),
            new TwigFunction('current_user', [$this, 'getCurrentUser']),
            new TwigFunction('is_logged_in', [$this, 'isLoggedIn']),
            new TwigFunction('is_admin', [$this, 'isAdmin']),
            new TwigFunction('is_customer', [$this, 'isCustomer']),
            new TwigFunction('product_image_src', [$this, 'getProductImageSrc']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('money', [$this, 'formatMoney']),
        ];
    }

    public function renderIcon(string $name): string
    {
        $allowed = ['arrow', 'bag', 'quality', 'comfort', 'style', 'care'];
        if (!in_array($name, $allowed, true)) {
            return '';
        }

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        return sprintf(
            '<img class="placeholder-icon placeholder-icon--%s" src="/assets/icons/%s-placeholder.svg" alt="" aria-hidden="true">',
            $safeName,
            $safeName
        );
    }

    public function formatMoney(float|int|string|null $amount): string
    {
        $val = (float) ($amount ?? 0);
        return '₱' . number_format($val, 2);
    }

    public function getProductImageSrc(?string $image): string
    {
        $img = trim((string) $image);
        if ($img === '') {
            return '/assets/images/black-tshirt-placeholder.svg';
        }
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        if (str_starts_with($img, 'assets/')) {
            return '/' . $img;
        }
        if (str_starts_with($img, '/')) {
            return $img;
        }
        return '/assets/images/' . $img;
    }

    public function getCartCount(): int
    {
        return $this->cartService->getCartCount();
    }

    public function getCurrentUser(): ?array
    {
        return $this->authService->getCurrentUser();
    }

    public function isLoggedIn(): bool
    {
        return $this->authService->isLoggedIn();
    }

    public function isAdmin(): bool
    {
        return $this->authService->isAdmin();
    }

    public function isCustomer(): bool
    {
        return $this->authService->isCustomer();
    }
}
