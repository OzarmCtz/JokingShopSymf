<?php

namespace App\Controller;

use App\Entity\Joke;
use App\Repository\CategoryRepository;
use App\Repository\JokeRepository;
use App\Service\RandomPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function home(Request $request, JokeRepository $jokeRepository, CategoryRepository $categoryRepository, PaginatorInterface $paginator): Response
    {
        // Récupération des paramètres
        $categoryId = $request->query->getInt('category') ?: null;
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $sort = $request->query->get('sort', 'newest');
        $page = $request->query->getInt('page', 1);

        // Si aucun filtre n'est appliqué (pas de catégorie, pas de prix), utiliser un tri aléatoire par défaut
        $hasFilters = $categoryId || ($minPrice !== null && $minPrice !== '') || ($maxPrice !== null && $maxPrice !== '');
        if (!$hasFilters && !$request->query->has('sort')) {
            $sort = 'random';
        }

        // Validation du tri
        $allowedSorts = ['newest', 'price_asc', 'price_desc', 'random'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'newest';
        }

        // Créer le query builder
        $queryBuilder = $jokeRepository->createActiveJokesQueryBuilder(
            $categoryId,
            $minPrice !== null && $minPrice !== '' ? (float) $minPrice : null,
            $maxPrice !== null && $maxPrice !== '' ? (float) $maxPrice : null,
            $sort === 'random' ? 'newest' : $sort // Utiliser 'newest' pour le tri random
        );

        // Pour le tri aléatoire, nous allons mélanger les résultats côté PHP
        if ($sort === 'random') {
            // Récupérer tous les résultats et les mélanger
            $allJokes = $queryBuilder->getQuery()->getResult();
            shuffle($allJokes);

            // Utiliser KnpPaginator sur le tableau mélangé
            $jokes = $paginator->paginate(
                $allJokes, // Passer directement le tableau
                $page,
                10,
                [
                    'pageParameterName' => 'page',
                    'sortFieldParameterName' => 'sortField',
                    'sortDirectionParameterName' => 'sortDirection',
                    'filterFieldParameterName' => 'filterField',
                    'filterValueParameterName' => 'filterValue',
                    'distinct' => false
                ]
            );
        } else {
            // Pagination normale avec KnpPaginator
            $jokes = $paginator->paginate(
                $queryBuilder->getQuery(),
                $page,
                10,
                [
                    'pageParameterName' => 'page',
                    'sortFieldParameterName' => 'sortField',
                    'sortDirectionParameterName' => 'sortDirection',
                    'filterFieldParameterName' => 'filterField',
                    'filterValueParameterName' => 'filterValue',
                    'distinct' => false
                ]
            );
        }

        $categories = $categoryRepository->findBy(['is_active' => true]);

        return $this->render('shop/home.html.twig', [
            'jokes' => $jokes,
            'categories' => $categories,
            'currentCategory' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'currentSort' => $sort,
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
