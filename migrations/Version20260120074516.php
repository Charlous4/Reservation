<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120074516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE session_membre (session_id INT NOT NULL, membre_id INT NOT NULL, PRIMARY KEY (session_id, membre_id))');
        $this->addSql('CREATE INDEX IDX_99DFF170613FECDF ON session_membre (session_id)');
        $this->addSql('CREATE INDEX IDX_99DFF1706A99F74A ON session_membre (membre_id)');
        $this->addSql('ALTER TABLE session_membre ADD CONSTRAINT FK_99DFF170613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_membre ADD CONSTRAINT FK_99DFF1706A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_membre DROP CONSTRAINT FK_99DFF170613FECDF');
        $this->addSql('ALTER TABLE session_membre DROP CONSTRAINT FK_99DFF1706A99F74A');
        $this->addSql('DROP TABLE session_membre');
    }
}
