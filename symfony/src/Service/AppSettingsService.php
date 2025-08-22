<?php

namespace App\Service;

use App\Entity\AppSettings;
use App\Repository\AppSettingsRepository;

class AppSettingsService
{
    private ?AppSettings $cachedSettings = null;

    public function __construct(
        private AppSettingsRepository $appSettingsRepository
    ) {}

    /**
     * Récupère les paramètres de l'application avec mise en cache
     */
    public function getSettings(): AppSettings
    {
        if ($this->cachedSettings === null) {
            $this->cachedSettings = $this->appSettingsRepository->getOrCreateAppSettings();
        }

        return $this->cachedSettings;
    }

    /**
     * Vide le cache des paramètres (utile après une modification)
     */
    public function clearCache(): void
    {
        $this->cachedSettings = null;
    }
}
