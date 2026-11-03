<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    #[Route('/customer/cart.php', name: 'app_cart_legacy', methods: ['GET'])]
    public function index(): Response
    {
        $cartData = $this->cartService->getItemsAndTotals();

        return $this->render('cart/index.html.twig', [
            'items' => $cartData['items'],
            'subtotal' => $cartData['subtotal'],
            'shipping' => $cartData['shipping'],
            'total' => $cartData['total'],
        ]);
    }

    #[Route('/cart/add', name: 'app_cart_add', methods: ['POST'])]
    #[Route('/actions/cart_process.php', name: 'app_cart_process_legacy', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $productId = (int) $request->request->get('product_id', 0);
        $action = (string) $request->request->get('action', 'add');
        $qty = max(1, (int) $request->request->get('quantity', 1));

        $isAjax = $request->isXmlHttpRequest() 
            || $request->request->get('ajax') === '1' 
            || str_contains($request->headers->get('Accept', ''), 'application/json');

        if ($action === 'remove') {
            $this->cartService->removeItem($productId);
            $message = 'Product removed from your bag.';
            $success = true;
        } else {
            $success = $this->cartService->addItem($productId, $qty);
            $message = $success ? 'Product added to your bag.' : 'Product is out of stock.';
        }

        if ($isAjax) {
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
                'cart_count' => $this->cartService->getCartCount(),
            ]);
        }

        $this->addFlash($success ? 'success' : 'error', $message);
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id, Request $request): Response
    {
        $this->cartService->removeItem($id);
        $this->addFlash('success', 'Product removed from your bag.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $qty = (int) $request->request->get('qty', 1);
        $this->cartService->updateItem($id, $qty);

        return $this->redirectToRoute('app_cart');
    }
}
