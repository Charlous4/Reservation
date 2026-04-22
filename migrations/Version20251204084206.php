<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204084206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscrire ADD membre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscrire ADD session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscrire ADD CONSTRAINT FK_84CA37A86A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE inscrire ADD CONSTRAINT FK_84CA37A8613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('CREATE INDEX IDX_84CA37A86A99F74A ON inscrire (membre_id)');
        $this->addSql('CREATE INDEX IDX_84CA37A8613FECDF ON inscrire (session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscrire DROP CONSTRAINT FK_84CA37A86A99F74A');
        $this->addSql('ALTER TABLE inscrire DROP CONSTRAINT FK_84CA37A8613FECDF');
        $this->addSql('DROP INDEX IDX_84CA37A86A99F74A');
        $this->addSql('DROP INDEX IDX_84CA37A8613FECDF');
        $this->addSql('ALTER TABLE inscrire DROP membre_id');
        $this->addSql('ALTER TABLE inscrire DROP session_id');
    }
}
