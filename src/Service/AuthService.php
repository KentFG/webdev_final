<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthService
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

    public function getCurrentUser(): ?array
    {
        $session = $this->getSession();
        if (!$session) {
            return null;
        }

        $userId = $session->get('user_id');
        if (!$userId) {
            return null;
        }

        $user = $this->dataStore->getUser((int) $userId);
        if (!$user || ($user['status'] ?? '') !== 'active') {
            $this->logout();
            return null;
        }

        return $user;
    }

    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    public function isCustomer(): bool
    {
        $user = $this->getCurrentUser();
        return $user && ($user['role'] ?? '') === 'customer';
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->dataStore->getUserByEmail($email);
        if (!$user || ($user['status'] ?? '') !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'] ?? '')) {
            return false;
        }

        $session = $this->getSession();
        if ($session) {
            $session->migrate(true);
            $session->set('user_id', (int) $user['id']);
            $session->set('role', $user['role']);
            $session->set('name', $user['name']);
        }

        return true;
    }

    public function signup(string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            return ['success' => false, 'error' => 'Name must be between 2 and 100 characters.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Please enter a valid email address.'];
        }

        if (mb_strlen($password) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
        }

        if ($this->dataStore->getUserByEmail($email)) {
            return ['success' => false, 'error' => 'An account with that email already exists.'];
        }

        $userId = $this->dataStore->saveUser([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->dataStore->saveProfile($userId, [
            'phone' => '',
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'province' => '',
            'postal_code' => '',
            'country' => 'Philippines',
        ]);

        $session = $this->getSession();
        if ($session) {
            $session->migrate(true);
            $session->set('user_id', $userId);
            $session->set('role', 'customer');
            $session->set('name', $name);
        }

        return ['success' => true, 'userId' => $userId];
    }

    public function logout(): void
    {
        $session = $this->getSession();
        if ($session) {
            $session->clear();
            $session->invalidate();
        }
    }
}
