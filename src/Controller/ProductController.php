<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{

    
    #[Route('/product/list', name: 'product_list')]
    public function listProduct(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();


        return $this->render('product/list.html.twig', ['products' => $products]);
    }




    #[Route('/product/create', name: 'product_create')]
    public function createProduct(Request $request, ProductRepository $productRepository): Response
    {

        if ($request->isMethod('POST')) {
            $product = new product();

            $product->setName($request->request->get("name"));
            $product->setDescription($request->request->get("description"));
            $product->setPrice($request->request->get("price"));

            $productRepository->save($product, true);

            dump($product);
        }

        return $this->render('product/index.html.twig');
    }

    #[Route('/product/update/{product}', name: 'product_update')]
    public function updateProduct(Product $product, Request $request, ProductRepository $productRepository): Response
    {

        dump($product);
        if ($request->isMethod('POST')) {
            $product = new product();

            $product->setName($request->request->get("name"));
            $product->setDescription($request->request->get("description"));
            $product->setPrice($request->request->get("price"));

            $productRepository->save($product, true);

            dump($product);
        }


        return $this->render('product/index.html.twig', ['product' => $product]);
    }


    #[Route('/product/delete/{product}', name: 'product_delete')]
    public function deleteProduct(Product $product): Response
    {

        dump($product);


        return $this->render('product/index.html.twig', ['product' => $product]);
    }
}
