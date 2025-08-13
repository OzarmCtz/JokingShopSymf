<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;

class AdminAccessSubscriber implements EventSubscriberInterface
{
    private SecurityBundle $security;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(SecurityBundle $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Vérifier si la route commence par /admin
        if (str_starts_with($request->getPathInfo(), '/admin')) {
            $user = $this->security->getUser();

            // Si l'utilisateur n'est pas connecté ou n'est pas admin
            if (!$user || !$this->security->isGranted('ROLE_ADMIN')) {
                // Rediriger vers la page de connexion
                $loginUrl = $this->urlGenerator->generate('app_login');
                $response = new RedirectResponse($loginUrl);
                $event->setResponse($response);
            }
        }
    }
}
