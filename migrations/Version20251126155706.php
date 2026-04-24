<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126155706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('CREATE INDEX IDX_B8755515C54C8C93 ON activite (type_id)');
        $this->addSql('ALTER TABLE membre ADD role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membre ADD CONSTRAINT FK_F6B4FB29D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
        $this->addSql('CREATE INDEX IDX_F6B4FB29D60322AC ON membre (role_id)');
        $this->addSql('ALTER TABLE session ADD activite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D49B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('CREATE INDEX IDX_D044D5D49B0F88B1 ON session (activite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP CONSTRAINT FK_B8755515C54C8C93');
        $this->addSql('DROP INDEX IDX_B8755515C54C8C93');
        $this->addSql('ALTER TABLE activite DROP type_id');
        $this->addSql('ALTER TABLE membre DROP CONSTRAINT FK_F6B4FB29D60322AC');
        $this->addSql('DROP INDEX IDX_F6B4FB29D60322AC');
        $this->addSql('ALTER TABLE membre DROP role_id');
        $this->addSql('ALTER TABLE session DROP CONSTRAINT FK_D044D5D49B0F88B1');
        $this->addSql('DROP INDEX IDX_D044D5D49B0F88B1');
        $this->addSql('ALTER TABLE session DROP activite_id');
    }
}
