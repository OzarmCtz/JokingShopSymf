<?php

namespace App\Controller;

use App\Entity\Joke;
use App\Repository\CategoryRepository;
use App\Repository\JokeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function home(Request $request, JokeRepository $jokeRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->getInt('category') ?: null;
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $sort = $request->query->get('sort', 'newest');

        $jokes = $jokeRepository->findActiveJokes(
            $categoryId,
            $minPrice !== null && $minPrice !== '' ? (float) $minPrice : null,
            $maxPrice !== null && $maxPrice !== '' ? (float) $maxPrice : null,
            $sort
        );

        $categories = $categoryRepository->findBy(['is_active' => true]);

        return $this->render('shop/home.html.twig', [
            'jokes' => $jokes,
            'categories' => $categories,
            'currentCategory' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    #[Route('/joke/{id}', name: 'shop_detail')]
    public function detail(Joke $joke): Response
    {
        if (!$joke->isActive()) {
            throw $this->createNotFoundException();
        }

        return $this->render('shop/detail.html.twig', [
            'joke' => $joke,
        ]);
    }
}
