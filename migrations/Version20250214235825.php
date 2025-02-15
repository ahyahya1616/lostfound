<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214235825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matches ADD user_id INT NOT NULL, DROP match_status');
        $this->addSql('ALTER TABLE matches ADD CONSTRAINT FK_62615BAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_62615BAA76ED395 ON matches (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matches DROP FOREIGN KEY FK_62615BAA76ED395');
        $this->addSql('DROP INDEX IDX_62615BAA76ED395 ON matches');
        $this->addSql('ALTER TABLE matches ADD match_status VARCHAR(255) NOT NULL, DROP user_id');
    }
}
