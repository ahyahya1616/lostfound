<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201005558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C641DA32');
        $this->addSql('DROP TABLE auth_provider');
        $this->addSql('DROP INDEX IDX_8D93D649C641DA32 ON user');
        $this->addSql('ALTER TABLE user ADD auth_provider VARCHAR(50) DEFAULT NULL, DROP auth_provider_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_provider (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user ADD auth_provider_id INT NOT NULL, DROP auth_provider');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C641DA32 FOREIGN KEY (auth_provider_id) REFERENCES auth_provider (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D649C641DA32 ON user (auth_provider_id)');
    }
}
