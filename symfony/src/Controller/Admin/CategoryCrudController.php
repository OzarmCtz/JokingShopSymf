<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie')
            ->setEntityLabelInPlural('Catégories')
            ->setSearchFields(['name', 'slug'])
            ->setDefaultSort(['created_at' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(function ($entity) {
                    // Vérifier s'il y a des blagues liées à cette catégorie
                    return $entity->getJokes()->isEmpty();
                });
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->displayIf(function ($entity) {
                    return $entity->getJokes()->isEmpty();
                });
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm()
            ->setLabel('ID');

        yield TextField::new('name')
            ->setLabel('Nom')
            ->setRequired(true);

        yield SlugField::new('slug')
            ->setTargetFieldName('name')
            ->setLabel('Slug')
            ->hideOnIndex();

        yield BooleanField::new('is_active')
            ->setLabel('Actif')
            ->renderAsSwitch();

        yield AssociationField::new('jokes')
            ->setLabel('Blagues')
            ->hideOnForm()
            ->formatValue(function ($value) {
                return $value ? count($value) . ' blague(s)' : '0 blague';
            });

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
            ->add(BooleanFilter::new('is_active')->setLabel('Actif'))
            ->add(DateTimeFilter::new('created_at')->setLabel('Date de création'));
    }
}
