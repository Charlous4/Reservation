<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120075253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_membre DROP CONSTRAINT fk_99dff1706a99f74a');
        $this->addSql('ALTER TABLE session_membre DROP CONSTRAINT fk_99dff170613fecdf');
        $this->addSql('DROP TABLE session_membre');
        $this->addSql('ALTER TABLE session ADD entraineur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4F8478A1 FOREIGN KEY (entraineur_id) REFERENCES membre (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D044D5D4F8478A1 ON session (entraineur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE session_membre (session_id INT NOT NULL, membre_id INT NOT NULL, PRIMARY KEY (session_id, membre_id))');
        $this->addSql('CREATE INDEX idx_99dff170613fecdf ON session_membre (session_id)');
        $this->addSql('CREATE INDEX idx_99dff1706a99f74a ON session_membre (membre_id)');
        $this->addSql('ALTER TABLE session_membre ADD CONSTRAINT fk_99dff1706a99f74a FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE session_membre ADD CONSTRAINT fk_99dff170613fecdf FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE session DROP CONSTRAINT FK_D044D5D4F8478A1');
        $this->addSql('DROP INDEX IDX_D044D5D4F8478A1');
        $this->addSql('ALTER TABLE session DROP entraineur_id');
    }
}
