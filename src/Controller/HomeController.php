<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DataStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private DataStore $dataStore;

    public function __construct(DataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        $products = array_slice($this->dataStore->getProducts(['active_only' => true]), 0, 5);

        $navItems = [
            ['label' => 'HOME', 'href' => '#home'],
            ['label' => 'ABOUT', 'href' => '#about'],
            ['label' => 'COLLECTION', 'href' => $this->generateUrl('app_collection')],
            ['label' => 'REVIEWS', 'href' => '#lookbook'],
            ['label' => 'CONTACT', 'href' => '#contact'],
        ];

        $values = [
            ['icon' => 'quality', 'title' => 'PREMIUM QUALITY', 'copy' => 'We use high-quality fabrics that are soft, durable, and built to last.'],
            ['icon' => 'comfort', 'title' => 'COMFORT FIRST', 'copy' => 'Designed for all-day comfort so you can move freely and feel your best.'],
            ['icon' => 'style', 'title' => 'TIMELESS STYLE', 'copy' => 'Minimal designs that never go out of style and easy to mix and match.'],
            ['icon' => 'care', 'title' => 'CARE IN EVERY ORDER', 'copy' => 'From packaging to delivery, we ensure a great experience every step of the way.'],
        ];

        $testimonials = [
            ['quote' => 'The quality is amazing! Super comfortable and the design is clean and easy to style.', 'name' => 'Mark D.'],
            ['quote' => 'Finally found a local brand that focuses on both style and comfort. Highly recommended!', 'name' => 'Jessa M.'],
            ['quote' => 'Fast shipping and great service. The fit is perfect. Will definitely order again!', 'name' => 'Kyle R.'],
        ];

        return $this->render('home/index.html.twig', [
            'navItems' => $navItems,
            'values' => $values,
            'testimonials' => $testimonials,
            'products' => $products,
        ]);
    }

    #[Route('/subscribe', name: 'app_subscribe', methods: ['POST'])]
    public function subscribe(Request $request): Response
    {
        $email = (string) $request->request->get('email', '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->dataStore->addSubscriber($email);
            $message = 'Thank you. You are on the list.';
            $success = true;
        } else {
            $message = 'Please enter a valid email address.';
            $success = false;
        }

        if ($request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json')) {
            return new JsonResponse(['success' => $success, 'message' => $message]);
        }

        $this->addFlash($success ? 'success' : 'error', $message);
        return $this->redirectToRoute('app_home');
    }
}
