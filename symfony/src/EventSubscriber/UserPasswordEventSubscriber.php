<?php

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UserPasswordEventSubscriber implements EventSubscriberInterface
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'handleUserUpdate',
            BeforeEntityPersistedEvent::class => 'handleUserCreate',
        ];
    }

    public function handleUserCreate(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        // Pour la création, hasher le mot de passe s'il existe
        if ($entity->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($entity, $entity->getPassword());
            $entity->setPassword($hashedPassword);
        }
    }

    public function handleUserUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $newPassword = null;

        // Extraire le nouveau mot de passe du formulaire
        if ($request && $request->isMethod('POST')) {
            $formData = $request->request->all();
            foreach ($formData as $data) {
                if (is_array($data) && isset($data['newPassword'])) {
                    $newPassword = $data['newPassword'];
                    break;
                }
            }
        }

        if (!empty($newPassword)) {
            // Nouveau mot de passe fourni : le hasher
            $hashedPassword = $this->passwordHasher->hashPassword($entity, $newPassword);
            $entity->setPassword($hashedPassword);
        } else {
            // Pas de nouveau mot de passe : conserver l'ancien
            $originalUser = $this->entityManager->find(User::class, $entity->getId());
            if ($originalUser) {
                $entity->setPassword($originalUser->getPassword());
            }
        }
    }
}
