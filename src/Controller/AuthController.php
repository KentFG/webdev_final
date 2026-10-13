<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    #[Route('/login.php', name: 'app_login_legacy', methods: ['GET', 'POST'])]
    #[Route('/actions/login_process.php', name: 'app_login_process_legacy', methods: ['POST'])]
    public function login(Request $request): Response
    {
        if ($this->authService->isLoggedIn()) {
            return $this->authService->isAdmin()
                ? $this->redirectToRoute('app_admin_dashboard')
                : $this->redirectToRoute('app_customer_profile');
        }

        $email = '';
        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email', ''));
            $password = (string) $request->request->get('password', '');

            if ($email === '' || $password === '') {
                $this->addFlash('error', 'Please enter your email and password.');
            } elseif ($this->authService->login($email, $password)) {
                $this->addFlash('success', 'Welcome back!');
                return $this->authService->isAdmin()
                    ? $this->redirectToRoute('app_admin_dashboard')
                    : $this->redirectToRoute('app_home');
            } else {
                $this->addFlash('error', 'Invalid email address or password.');
            }
        }

        return $this->render('auth/login.html.twig', [
            'email' => $email,
        ]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    #[Route('/signup.php', name: 'app_signup_legacy', methods: ['GET', 'POST'])]
    #[Route('/actions/signup_process.php', name: 'app_signup_process_legacy', methods: ['POST'])]
    public function signup(Request $request): Response
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirectToRoute('app_home');
        }

        $name = '';
        $email = '';

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $email = trim((string) $request->request->get('email', ''));
            $password = (string) $request->request->get('password', '');
            $confirmPassword = (string) $request->request->get('password_confirm', '');

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
            } else {
                $res = $this->authService->signup($name, $email, $password);
                if ($res['success']) {
                    $this->addFlash('success', 'Your account has been created successfully!');
                    return $this->redirectToRoute('app_home');
                }
                $this->addFlash('error', $res['error'] ?? 'Sign up failed.');
            }
        }

        return $this->render('auth/signup.html.twig', [
            'name' => $name,
            'email' => $email,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST', 'GET'])]
    #[Route('/logout.php', name: 'app_logout_legacy', methods: ['POST', 'GET'])]
    public function logout(): Response
    {
        $this->authService->logout();
        $this->addFlash('success', 'You have been logged out.');
        return $this->redirectToRoute('app_home');
    }
}
