<?php

namespace App\EventSubscriber;

use App\Service\AppSettingsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class AppSettingsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AppSettingsService $appSettingsService,
        private Environment $twig
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Rendre les paramètres disponibles globalement dans Twig
        $settings = $this->appSettingsService->getSettings();
        $this->twig->addGlobal('app_settings', $settings);
    }
}
