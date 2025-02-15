<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208175636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_object RENAME INDEX category_id TO IDX_78BA49CF12469DE2');
        $this->addSql('ALTER TABLE image_file DROP FOREIGN KEY FK_7EA5DC8E232D562B');
        $this->addSql('ALTER TABLE image_file ADD CONSTRAINT FK_7EA5DC8E232D562B FOREIGN KEY (object_id) REFERENCES abstract_object (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_object RENAME INDEX idx_78ba49cf12469de2 TO category_id');
        $this->addSql('ALTER TABLE image_file DROP FOREIGN KEY FK_7EA5DC8E232D562B');
        $this->addSql('ALTER TABLE image_file ADD CONSTRAINT FK_7EA5DC8E232D562B FOREIGN KEY (object_id) REFERENCES abstract_object (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
