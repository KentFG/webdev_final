<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\AuthService;
use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private AuthService $authService;
    private DataStore $dataStore;

    public function __construct(AuthService $authService, DataStore $dataStore)
    {
        $this->authService = $authService;
        $this->dataStore = $dataStore;
    }

    #[Route('/admin', name: 'app_admin_root', methods: ['GET'])]
    #[Route('/admin/dashboard', name: 'app_admin_dashboard', methods: ['GET'])]
    #[Route('/admin/dashboard.php', name: 'app_admin_dashboard_legacy', methods: ['GET'])]
    public function index(): Response
    {
        // Auth check temporarily disabled — no database/login yet.
        // if (!$this->authService->isAdmin()) {
        //     $this->addFlash('error', 'Access denied. Administrator privileges required.');
        //     return $this->redirectToRoute('app_login');
        // }

        $stats = $this->dataStore->getStats();
        $products = $this->dataStore->getProducts();
        $categories = $this->dataStore->getCategories();
        $recentOrders = array_slice($this->dataStore->getOrders(), 0, 10);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'products' => $products,
            'categories' => $categories,
            'recentOrders' => $recentOrders,
        ]);
    }
}
