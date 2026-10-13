<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\CartService;
use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    private AuthService $authService;
    private CartService $cartService;
    private DataStore $dataStore;
    private string $projectDir;

    public function __construct(AuthService $authService, CartService $cartService, DataStore $dataStore, string $projectDir)
    {
        $this->authService = $authService;
        $this->cartService = $cartService;
        $this->dataStore = $dataStore;
        $this->projectDir = $projectDir;
    }

    #[Route('/checkout', name: 'app_checkout', methods: ['GET'])]
    #[Route('/customer/checkout.php', name: 'app_checkout_legacy', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('error', 'Please log in to proceed to checkout.');
            return $this->redirectToRoute('app_login');
        }

        $cartData = $this->cartService->getItemsAndTotals();
        if (empty($cartData['items'])) {
            $this->addFlash('error', 'Your bag is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->authService->getCurrentUser();
        $profile = $this->dataStore->getProfile((int) $user['id']);

        return $this->render('checkout/index.html.twig', [
            'user' => $user,
            'profile' => $profile,
            'items' => $cartData['items'],
            'subtotal' => $cartData['subtotal'],
            'shipping' => $cartData['shipping'],
            'total' => $cartData['total'],
            'old' => array_merge($profile, ['name' => $user['name']]),
        ]);
    }

    #[Route('/checkout/process', name: 'app_checkout_process', methods: ['POST'])]
    #[Route('/actions/checkout_process.php', name: 'app_checkout_process_legacy', methods: ['POST'])]
    public function process(Request $request): Response
    {
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('error', 'Please log in to continue.');
            return $this->redirectToRoute('app_login');
        }

        $cartData = $this->cartService->getItemsAndTotals();
        if (empty($cartData['items'])) {
            $this->addFlash('error', 'Your bag is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->authService->getCurrentUser();
        $userId = (int) $user['id'];

        $name = trim((string) $request->request->get('name', ''));
        $phone = trim((string) $request->request->get('phone', ''));
        $address1 = trim((string) $request->request->get('address_line1', ''));
        $address2 = trim((string) $request->request->get('address_line2', ''));
        $city = trim((string) $request->request->get('city', ''));
        $province = trim((string) $request->request->get('province', ''));
        $postalCode = trim((string) $request->request->get('postal_code', ''));
        $country = trim((string) $request->request->get('country', 'Philippines'));
        $paymentMethod = (string) $request->request->get('payment_method', 'cod');
        $paymentReference = trim((string) $request->request->get('payment_reference', ''));
        $notes = trim((string) $request->request->get('notes', ''));

        $errors = [];
        if ($name === '') $errors[] = 'Full name is required.';
        if ($phone === '') $errors[] = 'Phone number is required.';
        if ($address1 === '') $errors[] = 'Address Line 1 is required.';
        if ($city === '') $errors[] = 'City is required.';
        if ($province === '') $errors[] = 'Province is required.';
        if ($postalCode === '') $errors[] = 'Postal code is required.';

        $receiptFilename = null;
        if ($paymentMethod === 'gcash') {
            /** @var UploadedFile|null $file */
            $file = $request->files->get('payment_receipt');
            if (!$file || !$file->isValid()) {
                $errors[] = 'Please upload a screenshot or photo of your GCash payment receipt.';
            } else {
                $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowed, true)) {
                    $errors[] = 'Invalid receipt format. Please upload JPG, PNG, or WEBP image.';
                } elseif ($file->getSize() > 10 * 1024 * 1024) {
                    $errors[] = 'Receipt file size must be less than 10MB.';
                } else {
                    $uploadDir = $this->projectDir . '/public/assets/uploads/receipts';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $receiptFilename = 'gcash_receipt_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $file->move($uploadDir, $receiptFilename);
                }
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $this->addFlash('error', $err);
            }
            return $this->redirectToRoute('app_checkout');
        }

        // Save profile updates
        $this->dataStore->saveProfile($userId, [
            'phone' => $phone,
            'address_line1' => $address1,
            'address_line2' => $address2,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postalCode,
            'country' => $country,
        ]);

        // Build order items
        $orderItems = [];
        foreach ($cartData['items'] as $item) {
            $orderItems[] = [
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'image' => $item['image'],
                'unit_price' => $item['price'],
                'quantity' => $item['qty'],
                'subtotal' => $item['subtotal'],
            ];
        }

        // Create order
        $orderId = $this->dataStore->saveOrder([
            'user_id' => $userId,
            'account_name' => $name,
            'email' => $user['email'],
            'total_amount' => $cartData['total'],
            'subtotal_amount' => $cartData['subtotal'],
            'shipping_fee' => $cartData['shipping'],
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_receipt' => $receiptFilename,
            'payment_reference' => $paymentReference,
            'payment_status' => 'pending',
            'driver_confirmation' => 'unconfirmed',
            'driver_name' => '',
            'driver_notes' => '',
            'shipping_name' => $name,
            'shipping_phone' => $phone,
            'shipping_address1' => $address1,
            'shipping_address2' => $address2,
            'shipping_city' => $city,
            'shipping_province' => $province,
            'shipping_postal_code' => $postalCode,
            'shipping_country' => $country,
            'notes' => $notes,
        ], $orderItems);

        // Clear bag
        $this->cartService->clearCart();

        $this->addFlash('success', 'Thank you! Your order #' . $orderId . ' has been placed successfully.');
        return $this->redirectToRoute('app_customer_order', ['id' => $orderId]);
    }
}
