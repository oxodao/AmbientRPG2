<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026155519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campaign (id SERIAL NOT NULL, owner_id INT NOT NULL, title VARCHAR(128) NOT NULL, game VARCHAR(128) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1512DD2B36786B ON campaign (title)');
        $this->addSql('CREATE INDEX IDX_1F1512DD7E3C61F9 ON campaign (owner_id)');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE campaign DROP CONSTRAINT FK_1F1512DD7E3C61F9');
        $this->addSql('DROP TABLE campaign');
    }
}
