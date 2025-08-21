<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-analytics',
    description: 'Test des analytics de commandes pour EasyAdmin',
)]
class TestAnalyticsCommand extends Command
{
    private OrderRepository $orderRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(OrderRepository $orderRepository, EntityManagerInterface $entityManager)
    {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test des Analytics de Commandes');

        // Test des statistiques de base
        $ordersTotal = $this->orderRepository->count([]);
        $ordersPaid = $this->orderRepository->count(['status' => 'paid']) + $this->orderRepository->count(['status' => 'succeeded']);

        $io->section('Statistiques générales');
        $io->text([
            "Commandes totales: $ordersTotal",
            "Commandes payées: $ordersPaid",
        ]);

        // Test du calcul de revenus
        $revenueTotal = $this->getTotalRevenue();
        $oneWeekAgo = new \DateTimeImmutable('-1 week');
        $revenueWeek = $this->getWeeklyRevenue($oneWeekAgo);
        $recentOrders = $this->getRecentOrdersCount($oneWeekAgo);

        $io->section('Statistiques financières');
        $io->text([
            "Revenus totaux: " . number_format($revenueTotal, 2) . "€",
            "Revenus de la semaine: " . number_format($revenueWeek, 2) . "€",
            "Nouvelles commandes (7 jours): $recentOrders",
        ]);

        $io->success('Analytics testées avec succès !');

        return Command::SUCCESS;
    }

    private function getTotalRevenue(): float
    {
        $connection = $this->entityManager->getConnection();
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
        $connection = $this->entityManager->getConnection();
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
        $connection = $this->entityManager->getConnection();
        $sql = 'SELECT COUNT(*) FROM `order` WHERE created_at >= :since';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('since', $oneWeekAgo->format('Y-m-d H:i:s'));
        $result = $stmt->executeQuery();

        return (int) $result->fetchOne();
    }
}
