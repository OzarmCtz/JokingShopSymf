<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250816132143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, joke_id INT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) NOT NULL, stripe_payment_intent_id VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, paid_at DATETIME DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, address LONGTEXT DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, region VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, card_holder_name VARCHAR(255) DEFAULT NULL, card_last4 VARCHAR(4) DEFAULT NULL, INDEX IDX_F529939830122C15 (joke_id), INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939830122C15 FOREIGN KEY (joke_id) REFERENCES joke (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939830122C15');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('DROP TABLE `order`');
    }
}
