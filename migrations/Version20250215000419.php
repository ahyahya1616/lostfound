<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215000419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matches DROP FOREIGN KEY FK_62615BA66D0A352');
        $this->addSql('ALTER TABLE matches DROP FOREIGN KEY FK_62615BACE04859D');
        $this->addSql('DROP INDEX IDX_62615BACE04859D ON matches');
        $this->addSql('DROP INDEX IDX_62615BA66D0A352 ON matches');
        $this->addSql('ALTER TABLE matches ADD object_id INT NOT NULL, DROP lost_object_id, DROP found_object_id');
        $this->addSql('ALTER TABLE matches ADD CONSTRAINT FK_62615BA232D562B FOREIGN KEY (object_id) REFERENCES abstract_object (id)');
        $this->addSql('CREATE INDEX IDX_62615BA232D562B ON matches (object_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matches DROP FOREIGN KEY FK_62615BA232D562B');
        $this->addSql('DROP INDEX IDX_62615BA232D562B ON matches');
        $this->addSql('ALTER TABLE matches ADD found_object_id INT NOT NULL, CHANGE object_id lost_object_id INT NOT NULL');
        $this->addSql('ALTER TABLE matches ADD CONSTRAINT FK_62615BA66D0A352 FOREIGN KEY (found_object_id) REFERENCES abstract_object (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE matches ADD CONSTRAINT FK_62615BACE04859D FOREIGN KEY (lost_object_id) REFERENCES abstract_object (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_62615BACE04859D ON matches (lost_object_id)');
        $this->addSql('CREATE INDEX IDX_62615BA66D0A352 ON matches (found_object_id)');
    }
}
