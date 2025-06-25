<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\ProductCommande;
use App\Repository\ProductCommandeRepository;
use App\Repository\ProductRepository;
use App\Repository\StatutsRepository;
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
        EntityManagerInterface $em,
        StatutsRepository $statutsRepository
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

        
        $statut = $statutsRepository->findBy(['Statut' => 'En attente'])[0] ?? null;


        $commande->setStatut($statut);

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
                'redirect' => ['url' => $_ENV['URL_BACK_STRIPE']],
            ],
        ]);

        return new JsonResponse([
            'url' => $paymentLink->url,
        ]);
    }

    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        EntityManagerInterface $em,
        ProductCommandeRepository $productCommandeRepository,
        StatutsRepository $statutsRepository
    ): JsonResponse {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $endpointSecret = "whsec_821512cdc1fd9c67d39bda73f8e9e1103bcf05a70c39e7f992afefbc28894d2b";

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new JsonResponse(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Vous pouvez ici faire correspondre la commande avec les informations du session ID ou autre champ
            // Exemple : mettre à jour le statut de la dernière commande
            $commande = $em->getRepository(Commande::class)->findOneBy([], ['id' => 'DESC']);

            if ($commande) {
                $statut = $statutsRepository->findBy(['Statut' => 'Confirmée'])[0];
                if ($statut) {
                    $commande->setStatut($statut);
                    $em->persist($commande);
                    $em->flush();
                }
            }
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
