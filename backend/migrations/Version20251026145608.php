<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026145608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE forgotten_password_request_id_seq CASCADE');
        $this->addSql('ALTER TABLE forgotten_password_request DROP CONSTRAINT fk_c02fc44da76ed395');
        $this->addSql('DROP TABLE forgotten_password_request');
        $this->addSql('ALTER TABLE app_users DROP password');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE forgotten_password_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE forgotten_password_request (id SERIAL NOT NULL, user_id INT NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, requested_from_ip VARCHAR(512) DEFAULT NULL, code VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c02fc44da76ed395 ON forgotten_password_request (user_id)');
        $this->addSql('COMMENT ON COLUMN forgotten_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN forgotten_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE forgotten_password_request ADD CONSTRAINT fk_c02fc44da76ed395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE app_users ADD password VARCHAR(512) DEFAULT NULL');
    }
}
