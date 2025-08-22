<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250822165413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_settings (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(255) NOT NULL, company_legal_form VARCHAR(255) NOT NULL, company_address LONGTEXT NOT NULL, company_postal_code VARCHAR(10) NOT NULL, company_city VARCHAR(100) NOT NULL, company_country VARCHAR(100) NOT NULL, company_siret VARCHAR(255) DEFAULT NULL, company_rcs VARCHAR(255) DEFAULT NULL, company_vat VARCHAR(255) DEFAULT NULL, company_capital VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(20) DEFAULT NULL, support_email VARCHAR(255) DEFAULT NULL, support_hours VARCHAR(255) NOT NULL, hosting_provider VARCHAR(255) DEFAULT NULL, hosting_address LONGTEXT DEFAULT NULL, hosting_phone VARCHAR(20) DEFAULT NULL, hosting_website VARCHAR(255) DEFAULT NULL, mediator_name VARCHAR(255) DEFAULT NULL, mediator_website VARCHAR(255) DEFAULT NULL, competent_court VARCHAR(100) NOT NULL, social_facebook VARCHAR(255) DEFAULT NULL, social_twitter VARCHAR(255) DEFAULT NULL, social_instagram VARCHAR(255) DEFAULT NULL, social_linkedin VARCHAR(255) DEFAULT NULL, dpo_email VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, publication_director VARCHAR(255) NOT NULL, website_domain VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_settings');
    }
}
