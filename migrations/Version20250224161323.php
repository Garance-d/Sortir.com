<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224161323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hangout_status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hangout CHANGE status status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E6BF700BD FOREIGN KEY (status_id) REFERENCES hangout_status (id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E6BF700BD ON hangout (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E6BF700BD');
        $this->addSql('DROP TABLE hangout_status');
        $this->addSql('DROP INDEX IDX_20C5B31E6BF700BD ON hangout');
        $this->addSql('ALTER TABLE hangout CHANGE status_id status INT DEFAULT NULL');
    }
}
