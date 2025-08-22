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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
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
use App\Service\UserAnonymizationService;
use App\Repository\OrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserAnonymizationService $anonymizationService,
        private OrderRepository $orderRepository
    ) {
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
        $anonymizeAction = Action::new('anonymizeAndDelete', 'Supprimer (avec anonymisation)', 'fas fa-user-slash')
            ->linkToCrudAction('anonymizeAndDelete')
            ->addCssClass('btn btn-danger')
            ->setHtmlAttributes(['onclick' => 'return confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ? Ses commandes seront anonymisées.")']);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $anonymizeAction)
            ->add(Crud::PAGE_DETAIL, $anonymizeAction)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Nouvel utilisateur');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Suppression simple')
                    ->addCssClass('btn btn-warning')
                    ->setHtmlAttributes(['onclick' => 'return confirm("Attention: Suppression sans anonymisation des commandes !")']);
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
            // Pour l'édition, afficher un champ mot de passe simple
            yield TextField::new('newPassword')
                ->setFormType(PasswordType::class)
                ->setLabel('Nouveau mot de passe')
                ->setRequired(false)
                ->setHelp('⚠️ Laissez VIDE pour conserver le mot de passe actuel')
                ->setFormTypeOptions([
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                ])
                ->hideOnIndex(); // Masquer sur la liste
        }

        // Afficher le nombre de commandes réussies (seulement sur l'index)
        if ($pageName === Crud::PAGE_INDEX) {
            yield IntegerField::new('successfulOrdersCount', 'Commandes réussies')
                ->setTemplatePath('admin/field/orders_count.html.twig')
                ->setSortable(false);
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

    public function anonymizeAndDelete(AdminContext $context): Response
    {
        $user = $context->getEntity()->getInstance();

        if (!$user instanceof User) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirect($context->getReferrer());
        }

        try {
            $result = $this->anonymizationService->deleteUserWithAnonymization($user);

            $this->addFlash('success', sprintf(
                'Utilisateur "%s" supprimé avec succès. %d commande(s) anonymisée(s).',
                $result['user_email'],
                $result['anonymized_orders_count']
            ));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirect($this->generateUrl('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]));
    }

    #[Route('/admin/user/{id}/orders-count', name: 'admin_user_orders_count', methods: ['GET'])]
    public function getOrdersCount(User $user): JsonResponse
    {
        $count = $this->orderRepository->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('(o.user = :user OR o.email = :email) AND o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('email', $user->getEmail())
            ->setParameter('status', 'paid')
            ->getQuery()
            ->getSingleScalarResult();

        return new JsonResponse(['count' => (int) $count]);
    }
}
