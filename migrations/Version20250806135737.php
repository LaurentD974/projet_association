<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250806135737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la relation entre user et referent avec referent_id nullable';
    }

    public function up(Schema $schema): void
    {
        // Modification des colonnes existantes
        $this->addSql('ALTER TABLE event CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE news CHANGE created_at created_at DATETIME NOT NULL');

        // Ajout de la colonne referent_id nullable
        $this->addSql('ALTER TABLE user ADD referent_id INT DEFAULT NULL');

        // Création de la contrainte et de l’index
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64935E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D64935E47E35 ON user (referent_id)');
    }

    public function down(Schema $schema): void
    {
        // Suppression de la relation et de la colonne
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64935E47E35');
        $this->addSql('DROP INDEX IDX_8D93D64935E47E35 ON user');
        $this->addSql('ALTER TABLE user DROP referent_id');

        // Restauration des colonnes modifiées
        $this->addSql('ALTER TABLE event CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE news CHANGE created_at created_at VARCHAR(255) NOT NULL');
    }
}