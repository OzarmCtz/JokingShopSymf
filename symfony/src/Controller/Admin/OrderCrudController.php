<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setSearchFields(['email', 'stripePaymentIntentId', 'cardHolderName'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW) // Les commandes ne peuvent pas être créées manuellement
            ->disable(Action::EDIT) // Les commandes ne peuvent pas être modifiées
            ->disable(Action::DELETE) // Les commandes ne peuvent pas être supprimées
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('Voir les détails');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('ID')
            ->hideOnForm();

        yield EmailField::new('email')
            ->setLabel('Email client');

        yield AssociationField::new('user')
            ->setLabel('Utilisateur associé')
            ->formatValue(function ($value) {
                return $value ? $value->getEmail() : 'Invité';
            });

        yield AssociationField::new('joke')
            ->setLabel('Blague achetée')
            ->formatValue(function ($value) {
                return $value ? $value->getTitle() ?: 'Blague #' . $value->getId() : 'N/A';
            });

        yield MoneyField::new('amount')
            ->setLabel('Montant')
            ->setCurrency('EUR')
            ->setStoredAsCents(true);

        yield ChoiceField::new('status')
            ->setLabel('Statut')
            ->setChoices([
                'En attente' => 'pending',
                'Payé' => 'paid',
                'Réussi' => 'succeeded',
                'Échoué' => 'failed',
                'Annulé' => 'canceled',
                'Remboursé' => 'refunded'
            ])
            ->renderAsBadges([
                'pending' => 'warning',
                'paid' => 'success',
                'succeeded' => 'success',
                'failed' => 'danger',
                'canceled' => 'secondary',
                'refunded' => 'info'
            ]);

        yield TextField::new('stripePaymentIntentId')
            ->setLabel('ID Paiement Stripe')
            ->hideOnIndex()
            ->formatValue(function ($value) {
                return $value ? substr($value, 0, 20) . '...' : 'N/A';
            });

        yield DateTimeField::new('createdAt')
            ->setLabel('Date de création')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('paidAt')
            ->setLabel('Date de paiement')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm()
            ->hideOnIndex();

        // Informations de facturation (visibles seulement en détail)
        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('cardHolderName')
                ->setLabel('Nom du porteur')
                ->hideOnIndex();

            yield TextField::new('cardLast4')
                ->setLabel('4 derniers chiffres')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return $value ? '**** **** **** ' . $value : 'N/A';
                });

            yield TextField::new('country')
                ->setLabel('Pays')
                ->hideOnIndex();

            yield TextField::new('address')
                ->setLabel('Adresse')
                ->hideOnIndex();

            yield TextField::new('city')
                ->setLabel('Ville')
                ->hideOnIndex();

            yield TextField::new('region')
                ->setLabel('Région')
                ->hideOnIndex();

            yield TextField::new('postalCode')
                ->setLabel('Code postal')
                ->hideOnIndex();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')->setChoices([
                'En attente' => 'pending',
                'Payé' => 'paid',
                'Réussi' => 'succeeded',
                'Échoué' => 'failed',
                'Annulé' => 'canceled',
                'Remboursé' => 'refunded'
            ])->setLabel('Statut'))
            ->add(EntityFilter::new('user')->setLabel('Utilisateur'))
            ->add(EntityFilter::new('joke')->setLabel('Blague'))
            ->add(DateTimeFilter::new('createdAt')->setLabel('Date de création'))
            ->add(DateTimeFilter::new('paidAt')->setLabel('Date de paiement'));
    }
}
