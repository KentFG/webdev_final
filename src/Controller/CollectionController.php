<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CollectionController extends AbstractController
{
    private DataStore $dataStore;

    public function __construct(DataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    #[Route('/collection', name: 'app_collection', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $categoryId = (int) $request->query->get('category', 0);

        $categories = $this->dataStore->getCategories();
        $products = $this->dataStore->getProducts([
            'active_only' => true,
            'search' => $q !== '' ? $q : null,
            'category_id' => $categoryId > 0 ? $categoryId : null,
        ]);

        return $this->render('collection/index.html.twig', [
            'q' => $q,
            'selectedCategory' => $categoryId,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
