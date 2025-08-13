<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Joke;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\JokeRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    private CategoryRepository $categoryRepository;
    private JokeRepository $jokeRepository;
    private UserRepository $userRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        JokeRepository $jokeRepository,
        UserRepository $userRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->jokeRepository = $jokeRepository;
        $this->userRepository = $userRepository;
    }

    public function index(): Response
    {
        $oneWeekAgo = new \DateTimeImmutable('-1 week');

        // Statistiques générales
        $categoriesCount = $this->categoryRepository->count([]);
        $jokesTotalCount = $this->jokeRepository->count([]);
        $jokesActiveCount = $this->jokeRepository->count(['is_active' => true]);
        $usersCount = $this->userRepository->count([]);

        // Statistiques récentes
        $recentJokes = $this->jokeRepository->countRecentJokes($oneWeekAgo);
        $recentUsers = $this->userRepository->countRecentUsers($oneWeekAgo);
        $recentCategories = $this->categoryRepository->countRecentCategories($oneWeekAgo);

        // Répartition des blagues par catégorie
        $jokesByCategory = $this->getJokesByCategory();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'categories_count' => $categoriesCount,
                'jokes_total' => $jokesTotalCount,
                'jokes_active' => $jokesActiveCount,
                'users_count' => $usersCount,
                'recent_jokes' => $recentJokes,
                'recent_users' => $recentUsers,
                'recent_categories' => $recentCategories,
                'jokes_by_category' => $jokesByCategory
            ]
        ]);
    }

    private function getJokesByCategory(): array
    {
        $categories = $this->categoryRepository->findAll();
        $totalJokes = $this->jokeRepository->count([]);
        $result = [];

        foreach ($categories as $category) {
            $count = $this->jokeRepository->count(['category' => $category]);
            if ($count > 0) {
                $result[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'count' => $count,
                    'percentage' => $totalJokes > 0 ? ($count / $totalJokes) * 100 : 0
                ];
            }
        }

        // Trier par nombre de blagues décroissant
        usort($result, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return $result;
    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du Blog')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Contenu');
        yield MenuItem::linkToCrud('Catégories', 'fas fa-folder', Category::class);
        yield MenuItem::linkToCrud('Blagues', 'fas fa-laugh', Joke::class);

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);

        yield MenuItem::section('Retour au site');
        yield MenuItem::linkToRoute('Accueil', 'fas fa-home', 'shop_home');
    }
}
