<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserAnonymizationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository
    ) {}

    /**
     * Anonymise toutes les commandes associées à un utilisateur
     */
    public function anonymizeUserOrders(User $user, bool $autoFlush = true): int
    {
        $orders = $this->orderRepository->findBy(['user' => $user]);
        $anonymizedCount = 0;

        foreach ($orders as $order) {
            // Anonymiser l'email
            $order->setEmail('anonymized_user_' . $order->getId() . '@deleted.local');

            // Retirer la relation avec l'utilisateur
            $order->setUser(null);

            $anonymizedCount++;
        }

        if ($anonymizedCount > 0 && $autoFlush) {
            $this->entityManager->flush();
        }

        return $anonymizedCount;
    }
    /**
     * Supprime un utilisateur et anonymise ses commandes
     */
    public function deleteUserWithAnonymization(User $user): array
    {
        $userEmail = $user->getEmail();

        // Utiliser une transaction pour s'assurer de la cohérence
        $this->entityManager->beginTransaction();

        try {
            // Anonymiser les commandes d'abord (sans flush automatique)
            $anonymizedCount = $this->anonymizeUserOrders($user, false);

            // Supprimer l'utilisateur
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            // Valider la transaction
            $this->entityManager->commit();

            return [
                'user_email' => $userEmail,
                'anonymized_orders_count' => $anonymizedCount
            ];
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
