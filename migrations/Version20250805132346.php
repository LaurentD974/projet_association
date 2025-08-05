<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250805132346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige la colonne description pour éviter les troncatures et ajoute les dates dans user';
    }

    public function up(Schema $schema): void
    {
        // Modification de la colonne description pour éviter les erreurs de troncature
        $this->addSql('ALTER TABLE event CHANGE type type VARCHAR(50) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        
        // Ajout des colonnes date_arrivee et date_depart dans la table user
        $this->addSql('ALTER TABLE user ADD date_arrivee DATE DEFAULT NULL, ADD date_depart DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Restauration des colonnes à leur état précédent
        $this->addSql('ALTER TABLE event CHANGE description description LONGTEXT DEFAULT NULL, CHANGE type type VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user DROP date_arrivee, DROP date_depart');
    }
}