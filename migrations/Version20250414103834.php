<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414103834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE beneficiary (id SERIAL NOT NULL, member_id INT NOT NULL, name VARCHAR(255) NOT NULL, bank_account_number VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7ABF446A7597D3FE ON beneficiary (member_id)');
        $this->addSql('ALTER TABLE beneficiary ADD CONSTRAINT FK_7ABF446A7597D3FE FOREIGN KEY (member_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE beneficiary DROP CONSTRAINT FK_7ABF446A7597D3FE');
        $this->addSql('DROP TABLE beneficiary');
    }
}
