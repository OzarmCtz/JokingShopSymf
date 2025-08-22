<?php

namespace App\Controller\Admin;

use App\Entity\AppSettings;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class AppSettingsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AppSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Configuration de l\'app')
            ->setEntityLabelInPlural('Configuration de l\'app')
            ->setPageTitle('index', 'Configuration de l\'application')
            ->setPageTitle('edit', 'Modifier la configuration')
            ->setPageTitle('detail', 'Configuration de l\'application')
            ->showEntityActionsInlined()
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(1) // Une seule ligne
            ->renderContentMaximized() // Plus d'espace
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Supprimer les actions de création et suppression
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            // Note: Pas besoin de supprimer DELETE sur PAGE_EDIT car elle n'existe pas par défaut
            // Personnaliser le bouton d'édition
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier')->setIcon('fa fa-edit');
            })
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // Champs pour la page d'index (résumé)
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('companyName', 'Entreprise');
            yield TextField::new('contactEmail', 'Email');
            yield TextField::new('companyCity', 'Ville');
            yield TextField::new('companyLegalForm', 'Forme juridique');
            return;
        }

        // Section Entreprise - Champs complets pour l'édition
        yield FormField::addPanel('Informations de l\'entreprise')->setIcon('fas fa-building');
        yield TextField::new('companyName', 'Nom de l\'entreprise')
            ->setHelp('Le nom commercial de votre entreprise')
            ->setRequired(true);

        yield TextareaField::new('companyDescription', 'Description')
            ->setHelp('Description courte de votre activité')
            ->setRequired(false);

        yield ChoiceField::new('companyLegalForm', 'Forme juridique')
            ->setChoices([
                'Micro-entreprise' => 'Micro-entreprise',
                'EURL' => 'EURL',
                'SARL' => 'SARL',
                'SAS' => 'SAS',
                'SASU' => 'SASU',
                'SA' => 'SA',
                'Association' => 'Association',
                'Autre' => 'Autre'
            ])
            ->setRequired(true);

        yield TextField::new('legalRepresentative', 'Représentant légal')
            ->setHelp('Nom et prénom du dirigeant/responsable légal')
            ->setRequired(false);

        // Section Adresse
        yield FormField::addPanel('Adresse du siège social')->setIcon('fas fa-map-marker-alt');

        // Section Adresse
        yield TextareaField::new('companyAddress', 'Adresse du siège social')
            ->setNumOfRows(3);

        yield TextField::new('companyPostalCode', 'Code postal');
        yield TextField::new('companyCity', 'Ville');
        yield TextField::new('companyCountry', 'Pays')
            ->setFormTypeOption('data', 'France')
            ->setRequired(true);

        // Section Informations légales
        yield FormField::addPanel('Informations légales et fiscales')->setIcon('fas fa-gavel');

        // Section Informations légales
        yield TextField::new('companySiret', 'SIRET')
            ->setHelp('Numéro SIRET de l\'entreprise');

        yield TextField::new('companyRcs', 'RCS')
            ->setHelp('Numéro RCS (si applicable)');

        yield TextField::new('companyVat', 'TVA intracommunautaire')
            ->setHelp('Numéro de TVA (si applicable)');

        yield TextField::new('companyCapital', 'Capital social')
            ->setHelp('Montant du capital (si applicable)');

        // Section Contact
        yield FormField::addPanel('Contact et support')->setIcon('fas fa-envelope');

        // Section Contact
        yield EmailField::new('contactEmail', 'Email de contact principal')
            ->setHelp('Email principal pour contacter l\'entreprise');

        yield TextField::new('contactPhone', 'Téléphone')
            ->setHelp('Numéro de téléphone principal');

        yield EmailField::new('supportEmail', 'Email support client')
            ->setHelp('Email dédié au support (optionnel)');

        yield TextField::new('supportHours', 'Horaires de support')
            ->setHelp('Ex: Lundi au vendredi, 9h-18h');

        // Section Hébergement
        yield FormField::addPanel('Informations d\'hébergement')->setIcon('fas fa-server');

        // Section Hébergement
        yield TextField::new('hostingProvider', 'Nom de l\'hébergeur')
            ->setHelp('Société qui héberge votre site web');

        yield TextareaField::new('hostingAddress', 'Adresse de l\'hébergeur')
            ->setNumOfRows(3);

        yield TextField::new('hostingPhone', 'Téléphone hébergeur');
        yield UrlField::new('hostingWebsite', 'Site web hébergeur');

        // Section Médiation et juridique
        yield FormField::addPanel('Médiation et juridique')->setIcon('fas fa-balance-scale');

        // Section Médiation
        yield TextField::new('mediatorName', 'Nom du médiateur')
            ->setHelp('Médiateur de la consommation (optionnel)');

        yield UrlField::new('mediatorWebsite', 'Site web médiateur');

        // Section Juridique
        yield TextField::new('competentCourt', 'Juridiction compétente')
            ->setHelp('Ville des tribunaux compétents');

        // Section Réseaux sociaux
        yield FormField::addPanel('Réseaux sociaux')->setIcon('fas fa-share-alt');

        // Section Réseaux sociaux
        yield UrlField::new('socialFacebook', 'Facebook')
            ->setHelp('URL de votre page Facebook');

        yield UrlField::new('socialTwitter', 'Twitter/X')
            ->setHelp('URL de votre profil Twitter');

        yield UrlField::new('socialInstagram', 'Instagram')
            ->setHelp('URL de votre profil Instagram');

        yield UrlField::new('socialLinkedin', 'LinkedIn')
            ->setHelp('URL de votre profil LinkedIn');
    }
}
