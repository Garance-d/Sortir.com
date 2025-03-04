<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303085428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campus (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, postal_code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, status_id INT DEFAULT NULL, host_id INT DEFAULT NULL, name VARCHAR(30) NOT NULL, start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', duration INT NOT NULL, registration_ends_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', max_users INT NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_3BAE0AA764D218E (location_id), INDEX IDX_3BAE0AA76BF700BD (status_id), INDEX IDX_3BAE0AA71FB8D185 (host_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, street VARCHAR(100) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, campus_id INT NOT NULL, first_name VARCHAR(30) NOT NULL, last_name VARCHAR(30) NOT NULL, phone VARCHAR(15) NOT NULL, email VARCHAR(50) NOT NULL, roles JSON NOT NULL, administrator TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, username VARCHAR(50) NOT NULL, INDEX IDX_8D93D649AF5D55E1 (campus_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_event (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D96CF1FFA76ED395 (user_id), INDEX IDX_D96CF1FF71F7E88B (event_id), PRIMARY KEY(user_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA76BF700BD FOREIGN KEY (status_id) REFERENCES event_status (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA71FB8D185 FOREIGN KEY (host_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id)');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FF71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA76BF700BD');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA71FB8D185');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649AF5D55E1');
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FFA76ED395');
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FF71F7E88B');
        $this->addSql('DROP TABLE campus');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_status');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_event');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
