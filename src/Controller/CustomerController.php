<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CustomerController extends AbstractController
{
    private AuthService $authService;
    private DataStore $dataStore;

    public function __construct(AuthService $authService, DataStore $dataStore)
    {
        $this->authService = $authService;
        $this->dataStore = $dataStore;
    }

    #[Route('/customer/orders', name: 'app_customer_orders', methods: ['GET'])]
    #[Route('/customer/orders.php', name: 'app_customer_orders_legacy', methods: ['GET'])]
    public function orders(): Response
    {
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('error', 'Please log in to view your orders.');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->authService->getCurrentUser();
        $orders = $this->dataStore->getOrders(['user_id' => (int) $user['id']]);

        return $this->render('customer/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/customer/order/{id}', name: 'app_customer_order', methods: ['GET'])]
    #[Route('/customer/order.php', name: 'app_customer_order_legacy', methods: ['GET'])]
    public function order(Request $request, ?int $id = null): Response
    {
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('error', 'Please log in to view order details.');
            return $this->redirectToRoute('app_login');
        }

        $orderId = $id ?: (int) $request->query->get('id', 0);
        $order = $this->dataStore->getOrder($orderId);
        $user = $this->authService->getCurrentUser();

        if (!$order || ((int) $order['user_id'] !== (int) $user['id'] && !$this->authService->isAdmin())) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_customer_orders');
        }

        return $this->render('customer/order.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/customer/profile', name: 'app_customer_profile', methods: ['GET', 'POST'])]
    #[Route('/customer/profile.php', name: 'app_customer_profile_legacy', methods: ['GET', 'POST'])]
    #[Route('/actions/profile_update.php', name: 'app_customer_profile_update_legacy', methods: ['POST'])]
    public function profile(Request $request): Response
    {
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('error', 'Please log in to view your profile.');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->authService->getCurrentUser();
        $userId = (int) $user['id'];
        $profile = $this->dataStore->getProfile($userId);

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $phone = trim((string) $request->request->get('phone', ''));
            $address1 = trim((string) $request->request->get('address_line1', ''));
            $address2 = trim((string) $request->request->get('address_line2', ''));
            $city = trim((string) $request->request->get('city', ''));
            $province = trim((string) $request->request->get('province', ''));
            $postalCode = trim((string) $request->request->get('postal_code', ''));
            $country = trim((string) $request->request->get('country', 'Philippines'));

            if ($name === '') {
                $this->addFlash('error', 'Full name is required.');
            } else {
                // Update user name
                $user['name'] = $name;
                $this->dataStore->saveUser($user);

                // Update profile
                $this->dataStore->saveProfile($userId, [
                    'phone' => $phone,
                    'address_line1' => $address1,
                    'address_line2' => $address2,
                    'city' => $city,
                    'province' => $province,
                    'postal_code' => $postalCode,
                    'country' => $country,
                ]);

                $this->addFlash('success', 'Profile updated successfully.');
                return $this->redirectToRoute('app_customer_profile');
            }
        }

        return $this->render('customer/profile.html.twig', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    #[Route('/faqs', name: 'app_faqs', methods: ['GET'])]
    #[Route('/customer/faqs.php', name: 'app_faqs_legacy', methods: ['GET'])]
    public function faqs(): Response
    {
        return $this->render('info/faqs.html.twig');
    }

    #[Route('/shipping', name: 'app_shipping', methods: ['GET'])]
    #[Route('/customer/shipping.php', name: 'app_shipping_legacy', methods: ['GET'])]
    public function shipping(): Response
    {
        return $this->render('info/shipping.html.twig');
    }

    #[Route('/size-guide', name: 'app_size_guide', methods: ['GET'])]
    #[Route('/customer/size-guide.php', name: 'app_size_guide_legacy', methods: ['GET'])]
    public function sizeGuide(): Response
    {
        return $this->render('info/size-guide.html.twig');
    }

    #[Route('/privacy', name: 'app_privacy', methods: ['GET'])]
    #[Route('/customer/privacy.php', name: 'app_privacy_legacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('info/privacy.html.twig');
    }

    #[Route('/terms', name: 'app_terms', methods: ['GET'])]
    #[Route('/customer/terms.php', name: 'app_terms_legacy', methods: ['GET'])]
    public function terms(): Response
    {
        return $this->render('info/terms.html.twig');
    }
}
