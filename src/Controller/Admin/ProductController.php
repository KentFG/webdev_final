<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\AuthService;
use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private AuthService $authService;
    private DataStore $dataStore;

    public function __construct(AuthService $authService, DataStore $dataStore)
    {
        $this->authService = $authService;
        $this->dataStore = $dataStore;
    }

    #[Route('/admin/product/new', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    #[Route('/admin/product_create.php', name: 'app_admin_product_create_legacy', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Auth check temporarily disabled — no database/login yet.
        // if (!$this->authService->isAdmin()) {
        //     $this->addFlash('error', 'Access denied.');
        //     return $this->redirectToRoute('app_login');
        // }

        $categories = $this->dataStore->getCategories();
        $product = [
            'id' => null,
            'name' => '',
            'category_id' => 0,
            'description' => '',
            'price' => 0.0,
            'stock' => 0,
            'image' => 'black-tshirt-placeholder.svg',
            'is_active' => 1,
        ];

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $categoryId = (int) $request->request->get('category_id', 0);
            $description = trim((string) $request->request->get('description', ''));
            $price = (float) $request->request->get('price', 0);
            $stock = (int) $request->request->get('stock', 0);
            $image = trim((string) $request->request->get('image', ''));
            $isActive = (int) ($request->request->get('is_active', 1));

            if ($name === '') {
                $this->addFlash('error', 'Product name is required.');
            } else {
                $productId = $this->dataStore->saveProduct([
                    'name' => $name,
                    'category_id' => $categoryId,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'image' => $image !== '' ? $image : 'black-tshirt-placeholder.svg',
                    'is_active' => $isActive,
                ]);

                $this->addFlash('success', 'Product created successfully.');
                return $this->redirectToRoute('app_admin_dashboard');
            }
        }

        return $this->render('admin/product_form.html.twig', [
            'product' => $product,
            'categories' => $categories,
            'isEdit' => false,
        ]);
    }

    #[Route('/admin/product/edit/{id}', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    #[Route('/admin/product_edit.php', name: 'app_admin_product_edit_legacy', methods: ['GET', 'POST'])]
    #[Route('/admin/product_update.php', name: 'app_admin_product_update_legacy', methods: ['POST'])]
    public function edit(Request $request, ?int $id = null): Response
    {
        // Auth check temporarily disabled — no database/login yet.
        // if (!$this->authService->isAdmin()) {
        //     $this->addFlash('error', 'Access denied.');
        //     return $this->redirectToRoute('app_login');
        // }

        $productId = $id ?: (int) $request->get('id', 0);
        $product = $this->dataStore->getProduct($productId);

        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $categories = $this->dataStore->getCategories();

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $categoryId = (int) $request->request->get('category_id', 0);
            $description = trim((string) $request->request->get('description', ''));
            $price = (float) $request->request->get('price', 0);
            $stock = (int) $request->request->get('stock', 0);
            $image = trim((string) $request->request->get('image', ''));
            $isActive = (int) ($request->request->has('is_active') ? $request->request->get('is_active') : 1);

            if ($name === '') {
                $this->addFlash('error', 'Product name is required.');
            } else {
                $this->dataStore->saveProduct([
                    'id' => $productId,
                    'name' => $name,
                    'category_id' => $categoryId,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'image' => $image !== '' ? $image : ($product['image'] ?? 'black-tshirt-placeholder.svg'),
                    'is_active' => $isActive,
                ]);

                $this->addFlash('success', 'Product updated successfully.');
                return $this->redirectToRoute('app_admin_dashboard');
            }
        }

        return $this->render('admin/product_form.html.twig', [
            'product' => $product,
            'categories' => $categories,
            'isEdit' => true,
        ]);
    }

    #[Route('/admin/product/delete/{id}', name: 'app_admin_product_delete', methods: ['POST'])]
    #[Route('/admin/product_delete.php', name: 'app_admin_product_delete_legacy', methods: ['POST'])]
    public function delete(Request $request, ?int $id = null): Response
    {
        if (!$this->authService->isAdmin()) {
            $this->addFlash('error', 'Access denied.');
            return $this->redirectToRoute('app_login');
        }

        $productId = $id ?: (int) $request->get('id', 0);
        $this->dataStore->deleteProduct($productId);
        $this->addFlash('success', 'Product deleted successfully.');

        return $this->redirectToRoute('app_admin_dashboard');
    }
}
