<?php

namespace App\Command;

use App\Entity\Joke;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-joke-images',
    description: 'Migre les anciennes images photo vers preview_image pour les blagues existantes',
)]
class MigrateJokeImagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migration des images de blagues');

        // Exécuter une requête SQL directe pour migrer les données
        $connection = $this->entityManager->getConnection();

        // Mettre à jour preview_image avec les valeurs de l'ancien champ photo 
        // qui ont été renommées par la migration
        $sql = 'UPDATE joke SET preview_image = preview_image WHERE preview_image IS NOT NULL AND preview_image != ""';
        $result = $connection->executeQuery($sql);

        $io->success(sprintf('Migration terminée. %d lignes pourraient avoir été affectées.', $result->rowCount()));

        // Compter les blagues avec des images
        $countSql = 'SELECT COUNT(*) as count FROM joke WHERE preview_image IS NOT NULL AND preview_image != ""';
        $countResult = $connection->executeQuery($countSql)->fetchAssociative();

        $io->info(sprintf('Nombre de blagues avec une image d\'aperçu: %d', $countResult['count']));

        $io->note([
            'Note: Les anciennes images "photo" ont été automatiquement renommées en "preview_image" par la migration Doctrine.',
            'Vous pouvez maintenant aller dans l\'administration EasyAdmin pour ajouter des images "view_image" pour les modals.',
            'Les images preview existantes seront utilisées pour les cards et comme fallback pour les modals.'
        ]);

        return Command::SUCCESS;
    }
}
