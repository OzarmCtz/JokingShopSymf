<?php

namespace App\Controller\Admin;

use App\Entity\Joke;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class JokeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Joke::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Blague')
            ->setEntityLabelInPlural('Blagues')
            ->setSearchFields(['title', 'body_text'])
            ->setDefaultSort(['created_at' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm()
            ->setLabel('ID');

        yield TextField::new('title')
            ->setLabel('Titre')
            ->setMaxLength(180);

        yield TextareaField::new('body_text')
            ->setLabel('Contenu')
            ->setNumOfRows(6)
            ->setRequired(true);

        yield AssociationField::new('category')
            ->setLabel('Catégorie')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, $entity) {
                return $value ? $value->getName() : '';
            });

        yield TextField::new('price')
            ->setLabel('Prix (€)')
            ->setHelp('Prix de la blague en euros (optionnel)');

        yield TextField::new('photo')
            ->setLabel('Photo')
            ->setHelp('URL ou nom du fichier photo (optionnel)');

        yield ChoiceField::new('language')
            ->setLabel('Langue')
            ->setChoices([
                'Français' => 'fr',
                'Anglais' => 'en',
                'Espagnol' => 'es',
                'Allemand' => 'de',
                'Italien' => 'it',
            ])
            ->allowMultipleChoices(false)
            ->renderExpanded(false);

        yield BooleanField::new('is_active')
            ->setLabel('Actif')
            ->renderAsSwitch();

        yield BooleanField::new('nsfw')
            ->setLabel('Contenu adulte (NSFW)')
            ->renderAsSwitch()
            ->setHelp('Cochez si le contenu n\'est pas approprié pour tous les publics');

        yield DateTimeField::new('created_at')
            ->setLabel('Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updated_at')
            ->setLabel('Modifié le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('category')->setLabel('Catégorie'))
            ->add(ChoiceFilter::new('language')->setChoices([
                'Français' => 'fr',
                'Anglais' => 'en',
                'Espagnol' => 'es',
                'Allemand' => 'de',
                'Italien' => 'it',
            ])->setLabel('Langue'))
            ->add(BooleanFilter::new('is_active')->setLabel('Actif'))
            ->add(BooleanFilter::new('nsfw')->setLabel('NSFW'))
            ->add(DateTimeFilter::new('created_at')->setLabel('Date de création'));
    }
}
