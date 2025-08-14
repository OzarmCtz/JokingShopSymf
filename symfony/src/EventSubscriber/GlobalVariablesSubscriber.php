<?php

namespace App\EventSubscriber;

use App\Repository\CategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class GlobalVariablesSubscriber implements EventSubscriberInterface
{
    private CategoryRepository $categoryRepository;
    private Environment $twig;

    public function __construct(CategoryRepository $categoryRepository, Environment $twig)
    {
        $this->categoryRepository = $categoryRepository;
        $this->twig = $twig;
    }

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

        $categories = $this->categoryRepository->findBy(['is_active' => true], ['name' => 'ASC']);
        $this->twig->addGlobal('global_categories', $categories);
    }
}
