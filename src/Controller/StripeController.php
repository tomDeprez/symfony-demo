<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index()
    {
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/apikeys
        $stripe = new \Stripe\StripeClient('');
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/apikeys

        // $product = $stripe->products->create(['name' => 'Per-seat']);
        // $product = $product->values();
        $price = $stripe->prices->create([
            'currency' => 'eur',
            'unit_amount' => 1000,
            'product_data' => ['name' => 'Gold Plan'],
        ]);
        $price = $price->values();
        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => [
                [
                    'price' => $price[0],
                    'quantity' => 1,
                ],
            ],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => 'https://example.com'],
            ],
        ]);

        $paymentLink = $paymentLink->values();

        return $this->redirect($paymentLink[31]);

        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }
}
