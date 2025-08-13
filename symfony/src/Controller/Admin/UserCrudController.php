<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Regex;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setSearchFields(['email', 'address'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Nouvel utilisateur');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm()
            ->setLabel('ID');

        yield EmailField::new('email')
            ->setLabel('Email')
            ->setRequired(true);

        // Champ mot de passe avec gestion spéciale pour l'édition
        if ($pageName === Crud::PAGE_NEW) {
            yield TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setLabel('Mot de passe')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir un mot de passe.',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères.',
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
                            'message' => 'Votre mot de passe doit contenir au moins une lettre et un chiffre.',
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a été exposé lors d’une fuite de données. Veuillez en choisir un autre.',
                        ]),
                    ],
                ]);
        } else {
            // Pour l'édition, on utilise un champ non mappé
            yield TextField::new('newPassword')
                ->setFormType(PasswordType::class)
                ->setLabel('Nouveau mot de passe')
                ->setRequired(false)
                ->setHelp('⚠️ Laissez VIDE pour conserver le mot de passe actuel')
                ->setFormTypeOptions([
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères.',
                            'max' => 4096,
                            'allowEmptyString' => true,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+|^$/',
                            'message' => 'Votre mot de passe doit contenir au moins une lettre et un chiffre.',
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a été exposé lors d’une fuite de données. Veuillez en choisir un autre.',
                            'skipOnEmpty' => true,
                        ]),
                    ],
                ]);
        }

        yield ChoiceField::new('roles')
            ->setLabel('Rôles')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices(true)
            ->renderExpanded(false)
            ->renderAsBadges([
                'ROLE_USER' => 'success',
                'ROLE_ADMIN' => 'danger'
            ]);

        yield TextField::new('address')
            ->setLabel('Adresse')
            ->hideOnIndex()
            ->setTemplatePath('admin/fields/address_autocomplete.html.twig')
            ->setFormTypeOptions([
                'attr' => [
                    'list' => 'address-suggestions',
                    'autocomplete' => 'off',
                ],
            ]);

        yield BooleanField::new('isVerified')
            ->setLabel('Email vérifié')
            ->renderAsSwitch();

        yield DateTimeField::new('createdAt')
            ->setLabel('Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updatedAt')
            ->setLabel('Modifié le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('roles')->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ])->setLabel('Rôle'))
            ->add(BooleanFilter::new('isVerified')->setLabel('Email vérifié'))
            ->add(DateTimeFilter::new('createdAt')->setLabel('Date de création'));
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // La gestion des mots de passe est gérée par UserPasswordEventSubscriber
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // La gestion des mots de passe est gérée par UserPasswordEventSubscriber
        parent::persistEntity($entityManager, $entityInstance);
    }
}
