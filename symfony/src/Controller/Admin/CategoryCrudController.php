<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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

        yield TextareaField::new('description')
            ->setLabel('Description')
            ->setNumOfRows(3)
            ->setRequired(false)
            ->hideOnIndex();

        yield SlugField::new('slug')
            ->setTargetFieldName('name')
            ->setLabel('Slug')
            ->hideOnIndex();

        yield TextField::new('color')
            ->setLabel('Couleur')
            ->setRequired(false)
            ->setHelp('Couleur en format hexadécimal (#RRGGBB), RGB (rgb(255,0,0)) ou RGBA (rgba(255,0,0,0.5))')
            ->setTemplatePath('admin/field/color_text.html.twig')
            ->setFormTypeOption('attr', [
                'placeholder' => 'Ex: #ff0000, rgb(255,0,0) ou rgba(255,0,0,0.5)',
                'pattern' => '^(#[0-9a-fA-F]{6}|rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)|rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[0-9.]+\s*\))$'
            ])
            ->formatValue(function ($value, $entity) {
                if (!$value) {
                    return 'Aucune couleur';
                }

                // Convertir les formats RGB/RGBA en style CSS valide
                $cssColor = $value;
                if (strpos($value, 'rgb') === 0) {
                    $cssColor = $value;
                } elseif (strpos($value, '#') === 0) {
                    $cssColor = $value;
                } else {
                    // Si ce n'est ni RGB ni hex, on utilise la valeur telle quelle
                    $cssColor = $value;
                }

                return sprintf(
                    '<div style="display: inline-flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background-color: %s; border: 1px solid #ccc; border-radius: 3px;"></div>
                        <span>%s</span>
                    </div>',
                    htmlspecialchars($cssColor, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
                );
            });

        yield TextField::new('icon')
            ->setLabel('Icône Font Awesome')
            ->setRequired(false)
            ->setHelp('Saisissez une classe Font Awesome (ex: fas fa-laugh). <a href="https://fontawesome.com/icons" target="_blank">📖 Voir la documentation Font Awesome</a>')
            ->formatValue(function ($value, $entity) {
                if (!$value) {
                    return 'Aucune icône';
                }
                return sprintf(
                    '<div style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="%s" style="font-size: 18px; width: 20px; text-align: center;"></i>
                        <span>%s</span>
                    </div>',
                    htmlspecialchars($value),
                    htmlspecialchars($value)
                );
            });

        yield ImageField::new('preview_image')
            ->setLabel('Image d\'aperçu (Card)')
            ->setBasePath('uploads/categories')
            ->setUploadDir('public/uploads/categories')
            ->setUploadedFileNamePattern('preview-[slug]-[uuid].[extension]')
            ->setRequired(false)
            ->setHelp('Image affichée sur les cartes de la boutique pour les jokes de cette catégorie (format recommandé: 240x280px)')
            ->hideOnIndex();

        yield ImageField::new('view_image')
            ->setLabel('Image de vue (Modal)')
            ->setBasePath('uploads/categories')
            ->setUploadDir('public/uploads/categories')
            ->setUploadedFileNamePattern('view-[slug]-[uuid].[extension]')
            ->setRequired(false)
            ->setHelp('Image affichée dans la modal de détails pour les jokes de cette catégorie (format recommandé: 400x400px ou plus)')
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
