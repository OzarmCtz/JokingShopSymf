<?php

namespace App\Entity;

use App\Repository\AppSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppSettingsRepository::class)]
class AppSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $companyDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalRepresentative = null;

    #[ORM\Column(length: 255)]
    private ?string $companyLegalForm = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $companyAddress = null;

    #[ORM\Column(length: 10)]
    private ?string $companyPostalCode = null;

    #[ORM\Column(length: 100)]
    private ?string $companyCity = null;

    #[ORM\Column(length: 100)]
    private ?string $companyCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companySiret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyRcs = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyVat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyCapital = null;

    #[ORM\Column(length: 255)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $supportEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $supportHours = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostingProvider = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $hostingAddress = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $hostingPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostingWebsite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediatorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediatorWebsite = null;

    #[ORM\Column(length: 100)]
    private ?string $competentCourt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialFacebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialTwitter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialInstagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialLinkedin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dpoEmail = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $publicationDirector = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $websiteDomain = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getCompanyDescription(): ?string
    {
        return $this->companyDescription;
    }

    public function setCompanyDescription(?string $companyDescription): static
    {
        $this->companyDescription = $companyDescription;
        return $this;
    }

    public function getLegalRepresentative(): ?string
    {
        return $this->legalRepresentative;
    }

    public function setLegalRepresentative(?string $legalRepresentative): static
    {
        $this->legalRepresentative = $legalRepresentative;
        return $this;
    }

    public function getCompanyLegalForm(): ?string
    {
        return $this->companyLegalForm;
    }

    public function setCompanyLegalForm(string $companyLegalForm): static
    {
        $this->companyLegalForm = $companyLegalForm;
        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;
        return $this;
    }

    public function getCompanyPostalCode(): ?string
    {
        return $this->companyPostalCode;
    }

    public function setCompanyPostalCode(string $companyPostalCode): static
    {
        $this->companyPostalCode = $companyPostalCode;
        return $this;
    }

    public function getCompanyCity(): ?string
    {
        return $this->companyCity;
    }

    public function setCompanyCity(string $companyCity): static
    {
        $this->companyCity = $companyCity;
        return $this;
    }

    public function getCompanyCountry(): ?string
    {
        return $this->companyCountry;
    }

    public function setCompanyCountry(string $companyCountry): static
    {
        $this->companyCountry = $companyCountry;
        return $this;
    }

    public function getCompanySiret(): ?string
    {
        return $this->companySiret;
    }

    public function setCompanySiret(?string $companySiret): static
    {
        $this->companySiret = $companySiret;
        return $this;
    }

    public function getCompanyRcs(): ?string
    {
        return $this->companyRcs;
    }

    public function setCompanyRcs(?string $companyRcs): static
    {
        $this->companyRcs = $companyRcs;
        return $this;
    }

    public function getCompanyVat(): ?string
    {
        return $this->companyVat;
    }

    public function setCompanyVat(?string $companyVat): static
    {
        $this->companyVat = $companyVat;
        return $this;
    }

    public function getCompanyCapital(): ?string
    {
        return $this->companyCapital;
    }

    public function setCompanyCapital(?string $companyCapital): static
    {
        $this->companyCapital = $companyCapital;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getSupportEmail(): ?string
    {
        return $this->supportEmail;
    }

    public function setSupportEmail(?string $supportEmail): static
    {
        $this->supportEmail = $supportEmail;
        return $this;
    }

    public function getSupportHours(): ?string
    {
        return $this->supportHours;
    }

    public function setSupportHours(string $supportHours): static
    {
        $this->supportHours = $supportHours;
        return $this;
    }

    public function getHostingProvider(): ?string
    {
        return $this->hostingProvider;
    }

    public function setHostingProvider(?string $hostingProvider): static
    {
        $this->hostingProvider = $hostingProvider;
        return $this;
    }

    public function getHostingAddress(): ?string
    {
        return $this->hostingAddress;
    }

    public function setHostingAddress(?string $hostingAddress): static
    {
        $this->hostingAddress = $hostingAddress;
        return $this;
    }

    public function getHostingPhone(): ?string
    {
        return $this->hostingPhone;
    }

    public function setHostingPhone(?string $hostingPhone): static
    {
        $this->hostingPhone = $hostingPhone;
        return $this;
    }

    public function getHostingWebsite(): ?string
    {
        return $this->hostingWebsite;
    }

    public function setHostingWebsite(?string $hostingWebsite): static
    {
        $this->hostingWebsite = $hostingWebsite;
        return $this;
    }

    public function getMediatorName(): ?string
    {
        return $this->mediatorName;
    }

    public function setMediatorName(?string $mediatorName): static
    {
        $this->mediatorName = $mediatorName;
        return $this;
    }

    public function getMediatorWebsite(): ?string
    {
        return $this->mediatorWebsite;
    }

    public function setMediatorWebsite(?string $mediatorWebsite): static
    {
        $this->mediatorWebsite = $mediatorWebsite;
        return $this;
    }

    public function getCompetentCourt(): ?string
    {
        return $this->competentCourt;
    }

    public function setCompetentCourt(string $competentCourt): static
    {
        $this->competentCourt = $competentCourt;
        return $this;
    }

    public function getSocialFacebook(): ?string
    {
        return $this->socialFacebook;
    }

    public function setSocialFacebook(?string $socialFacebook): static
    {
        $this->socialFacebook = $socialFacebook;
        return $this;
    }

    public function getSocialTwitter(): ?string
    {
        return $this->socialTwitter;
    }

    public function setSocialTwitter(?string $socialTwitter): static
    {
        $this->socialTwitter = $socialTwitter;
        return $this;
    }

    public function getSocialInstagram(): ?string
    {
        return $this->socialInstagram;
    }

    public function setSocialInstagram(?string $socialInstagram): static
    {
        $this->socialInstagram = $socialInstagram;
        return $this;
    }

    public function getSocialLinkedin(): ?string
    {
        return $this->socialLinkedin;
    }

    public function setSocialLinkedin(?string $socialLinkedin): static
    {
        $this->socialLinkedin = $socialLinkedin;
        return $this;
    }

    public function getDpoEmail(): ?string
    {
        return $this->dpoEmail;
    }

    public function setDpoEmail(?string $dpoEmail): static
    {
        $this->dpoEmail = $dpoEmail;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPublicationDirector(): ?string
    {
        return $this->publicationDirector;
    }

    public function setPublicationDirector(string $publicationDirector): static
    {
        $this->publicationDirector = $publicationDirector;
        return $this;
    }

    public function getWebsiteDomain(): ?string
    {
        return $this->websiteDomain;
    }

    public function setWebsiteDomain(?string $websiteDomain): static
    {
        $this->websiteDomain = $websiteDomain;
        return $this;
    }

    // Méthodes utilitaires
    public function getFullAddress(): string
    {
        return $this->companyAddress . "\n" .
            $this->companyPostalCode . " " . $this->companyCity . "\n" .
            $this->companyCountry;
    }

    public function getCompanyInfo(): string
    {
        $info = $this->companyName;
        if ($this->companyLegalForm) {
            $info .= " (" . $this->companyLegalForm . ")";
        }
        return $info;
    }

    public function __toString(): string
    {
        return $this->companyName ?? 'App Settings';
    }
}
