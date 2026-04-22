<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120094543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membre DROP CONSTRAINT fk_f6b4fb29d60322ac');
        $this->addSql('DROP INDEX idx_f6b4fb29d60322ac');
        $this->addSql('ALTER TABLE membre ADD roles JSON NOT NULL');
        $this->addSql('ALTER TABLE membre ADD password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE membre DROP mdp');
        $this->addSql('ALTER TABLE membre DROP mail');
        $this->addSql('ALTER TABLE membre DROP num_tel');
        $this->addSql('ALTER TABLE membre DROP role_id');
        $this->addSql('ALTER TABLE membre ALTER login TYPE VARCHAR(180)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F6B4FB29AA08CB10 ON membre (login)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_F6B4FB29AA08CB10');
        $this->addSql('ALTER TABLE membre ADD mail VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE membre ADD num_tel VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE membre ADD role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membre DROP roles');
        $this->addSql('ALTER TABLE membre ALTER login TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE membre RENAME COLUMN password TO mdp');
        $this->addSql('ALTER TABLE membre ADD CONSTRAINT fk_f6b4fb29d60322ac FOREIGN KEY (role_id) REFERENCES roles (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f6b4fb29d60322ac ON membre (role_id)');
    }
}
