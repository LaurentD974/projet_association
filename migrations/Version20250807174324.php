<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250807174324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64935E47E35');
        $this->addSql('DROP INDEX IDX_8D93D64935E47E35 ON user');
        $this->addSql('ALTER TABLE user DROP referent_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD referent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64935E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D64935E47E35 ON user (referent_id)');
    }
}
