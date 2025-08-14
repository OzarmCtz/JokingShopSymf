<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le changement de mot de passe si fourni
            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'profileForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/account/resend-verification', name: 'app_account_resend_verification', methods: ['POST'])]
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isVerified()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Votre email est déjà vérifié.'
            ], 400);
        }

        try {
            // Envoyer l'email de vérification
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('support@demo.fr', 'Support'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Un nouvel email de vérification a été envoyé à votre adresse email.'
            ]);
        } catch (\Exception $e) {
            // Log l'erreur pour debug
            error_log('Erreur envoi email: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de l\'email. Veuillez réessayer. Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
