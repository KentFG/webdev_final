<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\AuthService;
use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    private AuthService $authService;
    private DataStore $dataStore;

    public function __construct(AuthService $authService, DataStore $dataStore)
    {
        $this->authService = $authService;
        $this->dataStore = $dataStore;
    }

    #[Route('/admin/category/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    #[Route('/admin/category_create.php', name: 'app_admin_category_create_legacy', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Auth check temporarily disabled — no database/login yet.
        // if (!$this->authService->isAdmin()) {
        //     $this->addFlash('error', 'Access denied.');
        //     return $this->redirectToRoute('app_login');
        // }

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $description = trim((string) $request->request->get('description', ''));

            if ($name === '') {
                $this->addFlash('error', 'Category name is required.');
            } else {
                $this->dataStore->saveCategory([
                    'name' => $name,
                    'description' => $description,
                    'is_active' => 1,
                ]);

                $this->addFlash('success', 'Category created successfully.');
                return $this->redirectToRoute('app_admin_dashboard');
            }
        }

        return $this->render('admin/category_form.html.twig');
    }
}
