<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224151240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hangout ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E64D218E ON hangout (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E64D218E');
        $this->addSql('DROP INDEX IDX_20C5B31E64D218E ON hangout');
        $this->addSql('ALTER TABLE hangout DROP location_id');
    }
}
