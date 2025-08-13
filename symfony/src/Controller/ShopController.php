<?php

namespace App\Controller;

use App\Entity\Joke;
use App\Repository\CategoryRepository;
use App\Repository\JokeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function home(Request $request, JokeRepository $jokeRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->getInt('category') ?: null;
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');

        $jokes = $jokeRepository->findActiveJokes(
            $categoryId,
            $minPrice !== null && $minPrice !== '' ? (float) $minPrice : null,
            $maxPrice !== null && $maxPrice !== '' ? (float) $maxPrice : null
        );

        $categories = $categoryRepository->findBy(['is_active' => true]);

        return $this->render('shop/home.html.twig', [
            'jokes' => $jokes,
            'categories' => $categories,
            'currentCategory' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
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

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function addToCart(Joke $joke, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $joke->getId();

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'title' => $joke->getTitle(),
                'price' => (float) $joke->getPrice(),
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('shop_cart');
    }

    #[Route('/cart', name: 'shop_cart')]
    public function cart(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $this->render('shop/cart.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }
}
