<?php

namespace App\Repository;

use App\Entity\AppSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppSettings>
 */
class AppSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppSettings::class);
    }

    /**
     * Récupère les paramètres de l'application (il ne devrait y en avoir qu'un seul)
     */
    public function getAppSettings(): ?AppSettings
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère ou crée les paramètres de l'application
     */
    public function getOrCreateAppSettings(): AppSettings
    {
        $settings = $this->getAppSettings();

        if (!$settings) {
            $settings = new AppSettings();
            $settings->setCompanyName('Jo.King');
            $settings->setCompanyLegalForm('Micro-entreprise');
            $settings->setCompanyAddress('123 Rue de la Comédie');
            $settings->setCompanyPostalCode('75001');
            $settings->setCompanyCity('Paris');
            $settings->setCompanyCountry('France');
            $settings->setContactEmail('contact@jo-king.fr');
            $settings->setSupportHours('Lundi au vendredi, 9h-18h');
            $settings->setCompetentCourt('Paris');
            $settings->setPublicationDirector('Directeur Jo.King');
            $settings->setWebsiteDomain('jo-king.fr');
            $settings->setUpdatedAt(new \DateTime());

            $this->getEntityManager()->persist($settings);
            $this->getEntityManager()->flush();
        }

        return $settings;
    }

    public function save(AppSettings $entity, bool $flush = false): void
    {
        $entity->setUpdatedAt(new \DateTime());
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AppSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
