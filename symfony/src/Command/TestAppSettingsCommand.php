<?php

namespace App\Command;

use App\Service\AppSettingsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-settings',
    description: 'Test que les paramètres de l\'application sont bien chargés',
)]
class TestAppSettingsCommand extends Command
{
    public function __construct(
        private AppSettingsService $appSettingsService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $settings = $this->appSettingsService->getSettings();

        if (!$settings) {
            $io->error('Aucun paramètre d\'application trouvé en base de données !');
            return Command::FAILURE;
        }

        $io->success('Paramètres de l\'application chargés avec succès !');

        $io->section('Informations de l\'entreprise');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Nom de l\'entreprise', $settings->getCompanyName()],
                ['Description', $settings->getCompanyDescription() ?: 'Non défini'],
                ['SIRET', $settings->getCompanySiret() ?: 'Non défini'],
                ['Adresse', $settings->getCompanyAddress()],
                ['Code postal', $settings->getCompanyPostalCode()],
                ['Ville', $settings->getCompanyCity()],
                ['Forme juridique', $settings->getCompanyLegalForm()],
                ['Représentant légal', $settings->getLegalRepresentative() ?: 'Non défini'],
            ]
        );

        $io->section('Contact');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Email', $settings->getContactEmail()],
                ['Téléphone', $settings->getContactPhone() ?: 'Non défini'],
                ['Horaires support', $settings->getSupportHours() ?: 'Non défini'],
            ]
        );

        $io->section('Hébergement');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Fournisseur', $settings->getHostingProvider()],
                ['Adresse', $settings->getHostingAddress()],
                ['Site web', $settings->getHostingWebsite() ?: 'Non défini'],
            ]
        );

        $io->section('Réseaux sociaux');
        $socialNetworks = [
            ['Facebook', $settings->getSocialFacebook() ?: 'Non défini'],
            ['Twitter', $settings->getSocialTwitter() ?: 'Non défini'],
            ['Instagram', $settings->getSocialInstagram() ?: 'Non défini'],
            ['LinkedIn', $settings->getSocialLinkedin() ?: 'Non défini'],
        ];

        $io->table(['Réseau', 'URL'], $socialNetworks);

        return Command::SUCCESS;
    }
}
