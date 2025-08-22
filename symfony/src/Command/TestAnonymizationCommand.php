<?php

namespace App\Command;

use App\Service\UserAnonymizationService;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-anonymization',
    description: 'Test l\'anonymisation des commandes d\'un utilisateur',
)]
class TestAnonymizationCommand extends Command
{
    public function __construct(
        private UserAnonymizationService $anonymizationService,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user-email', InputArgument::REQUIRED, 'Email de l\'utilisateur à tester')
            ->setHelp('Cette commande teste l\'anonymisation des commandes d\'un utilisateur sans le supprimer.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getArgument('user-email');

        $user = $this->userRepository->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $io->error(sprintf('Utilisateur avec l\'email "%s" non trouvé.', $userEmail));
            return Command::FAILURE;
        }

        $io->note(sprintf('Test d\'anonymisation pour l\'utilisateur : %s (ID: %d)', $user->getEmail(), $user->getId()));

        try {
            // Test uniquement l'anonymisation des commandes (sans suppression)
            $anonymizedCount = $this->anonymizationService->anonymizeUserOrders($user);

            $io->success(sprintf(
                'Test réussi ! %d commande(s) ont été anonymisées pour l\'utilisateur "%s".',
                $anonymizedCount,
                $userEmail
            ));

            if ($anonymizedCount === 0) {
                $io->note('Aucune commande trouvée pour cet utilisateur.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors du test d\'anonymisation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
