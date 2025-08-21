<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Joke;
use App\Entity\User;
use App\Entity\Order;
use App\Repository\CategoryRepository;
use App\Repository\JokeRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
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
    private OrderRepository $orderRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        JokeRepository $jokeRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->jokeRepository = $jokeRepository;
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
    }

    public function index(): Response
    {
        $oneWeekAgo = new \DateTimeImmutable('-1 week');

        // Statistiques générales
        $categoriesCount = $this->categoryRepository->count([]);
        $jokesTotalCount = $this->jokeRepository->count([]);
        $jokesActiveCount = $this->jokeRepository->count(['is_active' => true]);
        $usersCount = $this->userRepository->count([]);

        // Statistiques de commandes
        $ordersTotal = $this->orderRepository->count([]);
        $ordersPaid = $this->orderRepository->count(['status' => 'paid']) + $this->orderRepository->count(['status' => 'succeeded']);
        $revenueTotal = $this->getTotalRevenue();
        $revenueWeek = $this->getWeeklyRevenue($oneWeekAgo);

        // Statistiques récentes
        $recentJokes = $this->jokeRepository->countRecentJokes($oneWeekAgo);
        $recentUsers = $this->userRepository->countRecentUsers($oneWeekAgo);
        $recentCategories = $this->categoryRepository->countRecentCategories($oneWeekAgo);
        $recentOrders = $this->getRecentOrdersCount($oneWeekAgo);

        // Répartition des blagues par catégorie
        $jokesByCategory = $this->getJokesByCategory();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'categories_count' => $categoriesCount,
                'jokes_total' => max(0, $jokesTotalCount),
                'jokes_active' => max(0, $jokesActiveCount),
                'users_count' => $usersCount,
                'orders_total' => $ordersTotal,
                'orders_paid' => $ordersPaid,
                'revenue_total' => $revenueTotal,
                'revenue_week' => $revenueWeek,
                'recent_jokes' => $recentJokes,
                'recent_users' => $recentUsers,
                'recent_categories' => $recentCategories,
                'recent_orders' => $recentOrders,
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

    private function getTotalRevenue(): float
    {
        $connection = $this->orderRepository->getEntityManager()->getConnection();
        $sql = 'SELECT SUM(amount) as total FROM `order` WHERE status IN (:paid, :succeeded)';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('paid', 'paid');
        $stmt->bindValue('succeeded', 'succeeded');
        $result = $stmt->executeQuery();

        $totalCents = (float) $result->fetchOne() ?: 0.0;
        return $totalCents / 100; // Convertir de centimes en euros
    }

    private function getWeeklyRevenue(\DateTimeImmutable $oneWeekAgo): float
    {
        $connection = $this->orderRepository->getEntityManager()->getConnection();
        $sql = 'SELECT SUM(amount) as total FROM `order` WHERE status IN (:paid, :succeeded) AND created_at >= :since';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('paid', 'paid');
        $stmt->bindValue('succeeded', 'succeeded');
        $stmt->bindValue('since', $oneWeekAgo->format('Y-m-d H:i:s'));
        $result = $stmt->executeQuery();

        $totalCents = (float) $result->fetchOne() ?: 0.0;
        return $totalCents / 100; // Convertir de centimes en euros
    }
    private function getRecentOrdersCount(\DateTimeImmutable $oneWeekAgo): int
    {
        $connection = $this->orderRepository->getEntityManager()->getConnection();
        $sql = 'SELECT COUNT(*) FROM `order` WHERE created_at >= :since';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('since', $oneWeekAgo->format('Y-m-d H:i:s'));
        $result = $stmt->executeQuery();

        return (int) $result->fetchOne();
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

        yield MenuItem::section('Commerce');
        yield MenuItem::linkToCrud('Commandes', 'fas fa-shopping-cart', Order::class);

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);

        yield MenuItem::section('Retour au site');
        yield MenuItem::linkToRoute('Accueil', 'fas fa-home', 'shop_home');
    }
}
