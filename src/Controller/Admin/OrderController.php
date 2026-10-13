<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\AuthService;
use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    private AuthService $authService;
    private DataStore $dataStore;

    public function __construct(AuthService $authService, DataStore $dataStore)
    {
        $this->authService = $authService;
        $this->dataStore = $dataStore;
    }

    #[Route('/admin/orders', name: 'app_admin_orders', methods: ['GET'])]
    #[Route('/admin/orders.php', name: 'app_admin_orders_legacy', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Auth check temporarily disabled — no database/login yet.
        // if (!$this->authService->isAdmin()) {
        //     $this->addFlash('error', 'Access denied.');
        //     return $this->redirectToRoute('app_login');
        // }

        $status = trim((string) $request->query->get('status', ''));
        $orders = $this->dataStore->getOrders($status !== '' ? ['status' => $status] : []);

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
            'selectedStatus' => $status,
        ]);
    }

    #[Route('/admin/order/{id}', name: 'app_admin_order', methods: ['GET'])]
    #[Route('/admin/order.php', name: 'app_admin_order_legacy', methods: ['GET'])]
    public function view(Request $request, ?int $id = null): Response
    {
        // Auth check temporarily disabled — no database/login yet.
        // if (!$this->authService->isAdmin()) {
        //     $this->addFlash('error', 'Access denied.');
        //     return $this->redirectToRoute('app_login');
        // }

        $orderId = $id ?: (int) $request->query->get('id', 0);
        $order = $this->dataStore->getOrder($orderId);

        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_admin_orders');
        }

        return $this->render('admin/order.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/admin/order/{id}/update', name: 'app_admin_order_update', methods: ['POST'])]
    #[Route('/admin/order_update.php', name: 'app_admin_order_update_legacy', methods: ['POST'])]
    public function update(Request $request, ?int $id = null): Response
    {
        if (!$this->authService->isAdmin()) {
            $this->addFlash('error', 'Access denied.');
            return $this->redirectToRoute('app_login');
        }

        $orderId = $id ?: (int) $request->request->get('order_id', 0);
        $status = (string) $request->request->get('status', 'pending');
        $paymentStatus = (string) $request->request->get('payment_status', 'pending');
        $driverConfirmation = (string) $request->request->get('driver_confirmation', 'unconfirmed');
        $driverName = trim((string) $request->request->get('driver_name', ''));
        $driverNotes = trim((string) $request->request->get('driver_notes', ''));

        $this->dataStore->updateOrder($orderId, [
            'status' => $status,
            'payment_status' => $paymentStatus,
            'driver_confirmation' => $driverConfirmation,
            'driver_name' => $driverName,
            'driver_notes' => $driverNotes,
            'driver_confirmed_at' => $driverConfirmation === 'confirmed' ? date('Y-m-d H:i:s') : null,
        ]);

        $this->addFlash('success', 'Order #' . $orderId . ' updated successfully.');
        return $this->redirectToRoute('app_admin_order', ['id' => $orderId]);
    }
}
