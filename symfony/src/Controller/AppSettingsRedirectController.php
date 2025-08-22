<?php

namespace App\Controller;

use App\Repository\AppSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppSettingsRedirectController extends AbstractController
{
    #[Route('/admin/app-settings', name: 'admin_app_settings_redirect')]
    public function redirectToEdit(AppSettingsRepository $repository): Response
    {
        $settings = $repository->getOrCreateAppSettings();

        return $this->redirectToRoute('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => 'App\\Controller\\Admin\\AppSettingsCrudController',
            'entityId' => $settings->getId()
        ]);
    }
}
