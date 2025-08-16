<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Form\ProfileEditFormType;
use App\Security\EmailVerifier;
use App\Service\JokeEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private JokeEmailService $jokeEmailService
    ) {}

    #[Route('/account', name: 'app_account')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Récupérer les commandes de l'utilisateur
        $orders = $entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->where('o.user = :user OR o.email = :email')
            ->setParameter('user', $user)
            ->setParameter('email', $user->getEmail())
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            if ($currentPassword && !$userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
            } else {
                $newPassword = $form->get('plainPassword')->getData();
                if ($newPassword) {
                    $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
                }

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

                return $this->redirectToRoute('app_account');
            }
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/account/resend-joke/{orderId}', name: 'app_account_resend_joke', methods: ['POST'])]
    public function resendJoke(int $orderId, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Validation CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('resend-joke-' . $orderId, $token)) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_account');
        }

        // Récupérer la commande et vérifier qu'elle appartient à l'utilisateur
        $order = $entityManager->getRepository(Order::class)->find($orderId);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        // Vérifier que la commande appartient à l'utilisateur (par association ou par email)
        if ($order->getUser() !== $user && $order->getEmail() !== $user->getEmail()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette commande');
        }

        if ($order->getStatus() !== 'paid') {
            $this->addFlash('error', 'Cette commande n\'est pas payée');
            return $this->redirectToRoute('app_account');
        }

        try {
            // Envoyer l'email avec la blague
            $this->jokeEmailService->sendJokeEmail($order);
            $this->addFlash('success', 'La blague a été renvoyée à votre adresse email');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/account/email/verify', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_account');
    }
}
