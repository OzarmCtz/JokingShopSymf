<?php

namespace App\Command;

use App\Entity\Order;
use App\Entity\Joke;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-test-orders',
    description: 'Génère des commandes de test pour EasyAdmin',
)]
class GenerateTestOrdersCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('count', InputArgument::OPTIONAL, 'Nombre de commandes à générer', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = (int) $input->getArgument('count');

        $io->title('Génération de commandes de test');

        // Récupérer les jokes et utilisateurs existants
        $jokes = $this->entityManager->getRepository(Joke::class)->findAll();
        $users = $this->entityManager->getRepository(User::class)->findAll();

        if (empty($jokes)) {
            $io->error('Aucune blague trouvée. Veuillez d\'abord créer des blagues.');
            return Command::FAILURE;
        }

        $statuses = ['pending', 'paid', 'succeeded', 'failed', 'canceled'];
        $emails = [
            'test1@example.com',
            'test2@example.com',
            'client@demo.com',
            'buyer@test.fr',
            'customer@example.org'
        ];

        $io->progressStart($count);

        for ($i = 0; $i < $count; $i++) {
            $order = new Order();

            // Email aléatoire
            $order->setEmail($emails[array_rand($emails)]);

            // Joke aléatoire
            $randomJoke = $jokes[array_rand($jokes)];
            $order->setJoke($randomJoke);

            // Utilisateur aléatoire (parfois null pour simuler des invités)
            if (!empty($users) && rand(0, 1)) {
                $randomUser = $users[array_rand($users)];
                $order->setUser($randomUser);
            }

            // Montant aléatoire
            $order->setAmount(number_format(rand(99, 999) / 10, 2, '.', ''));

            // Statut aléatoire
            $status = $statuses[array_rand($statuses)];
            $order->setStatus($status);

            // ID Stripe fake
            $order->setStripePaymentIntentId('pi_' . uniqid() . '_fake');

            // Date de création aléatoire dans les 30 derniers jours
            $randomDays = rand(0, 30);
            $createdAt = new \DateTime('-' . $randomDays . ' days');
            $order->setCreatedAt($createdAt);

            // Date de paiement si payé
            if (in_array($status, ['paid', 'succeeded'])) {
                $paidAt = clone $createdAt;
                $paidAt->modify('+' . rand(1, 60) . ' minutes');
                $order->setPaidAt($paidAt);
            }

            // Informations de facturation aléatoires
            $order->setCountry('FR');
            $order->setCardHolderName('Test User ' . ($i + 1));
            $order->setCardLast4(str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT));
            $order->setCity('Paris');
            $order->setPostalCode('75000');
            $order->setAddress('123 Rue de Test');

            $this->entityManager->persist($order);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success("$count commandes de test générées avec succès !");

        return Command::SUCCESS;
    }
}
