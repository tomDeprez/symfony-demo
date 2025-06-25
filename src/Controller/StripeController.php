<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\ProductCommande;
use App\Repository\ProductCommandeRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use \DateTime;

final class StripeController extends AbstractController
{
    #[Route('/stripe/create/link', name: 'app_stripe', methods: ['POST'])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        ProductCommandeRepository $productCommandeRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $content = json_decode($request->getContent(), true);
        $order = $content['order'] ?? [];

        if (empty($order)) {
            return new JsonResponse(['error' => 'Empty order'], 400);
        }

        $commande = new Commande();
        $commande->setDate(new \DateTime());


        foreach ($order as $item) {
            $product = $productRepository->find($item['id']);
            if (!$product) {
                continue;
            }

            $commandeProduct = new ProductCommande();
            $commandeProduct->setPrice($product->getPrice());
            $commandeProduct->setName($product->getName());
            $commandeProduct->setQuantity($item['quantity']);
            

            $commande->addProductCommande($commandeProduct);
            $productCommandeRepository->save($commandeProduct);
        }

        $em->persist($commande);
        $em->flush();

        $stripeKeyS = $_ENV['StripeKeyS'];
        $stripe = new StripeClient($stripeKeyS);

        $lineItems = [];

        foreach ($order as $item) {
            $price = $stripe->prices->create([
                'currency' => 'eur',
                'unit_amount' => $item['price'] * 100,
                'product_data' => ['name' => $item['name']],
            ]);

            $lineItems[] = [
                'price' => $price->id,
                'quantity' => $item['quantity'],
            ];
        }

        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => $lineItems,
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => 'http://localhost:8000/success/link'],
            ],
        ]);

        return new JsonResponse([
            'url' => $paymentLink->url,
        ]);
    }
}
