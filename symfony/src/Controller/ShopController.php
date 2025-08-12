<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    private array $articles = [
        [
            'id' => 1,
            'name' => 'Article 1',
            'price' => 19.99,
            'description' => 'Description 1',
            'image' => 'https://via.placeholder.com/300x200?text=Article+1'
        ],
        [
            'id' => 2,
            'name' => 'Article 2',
            'price' => 29.99,
            'description' => 'Description 2',
            'image' => 'https://via.placeholder.com/300x200?text=Article+2'
        ],
        [
            'id' => 3,
            'name' => 'Article 3',
            'price' => 39.99,
            'description' => 'Description 3',
            'image' => 'https://via.placeholder.com/300x200?text=Article+3'
        ]
    ];

    #[Route('/', name: 'shop_home')]
    public function home(): Response
    {
        return $this->render('shop/home.html.twig', [
            'articles' => $this->articles
        ]);
    }

    #[Route('/article/{id}', name: 'shop_detail')]
    public function detail(int $id): Response
    {
        $article = array_filter($this->articles, fn($a) => $a['id'] === $id);
        $article = reset($article);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('shop/detail.html.twig', [
            'article' => $article
        ]);
    }

    #[Route('/cart', name: 'shop_cart')]
    public function cart(): Response
    {
        return $this->render('shop/cart.html.twig');
    }
}
