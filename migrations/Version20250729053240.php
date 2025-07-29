<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729053240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD prenom VARCHAR(100) DEFAULT NULL, ADD nom VARCHAR(100) DEFAULT NULL, ADD metier VARCHAR(100) DEFAULT NULL, ADD statut VARCHAR(100) DEFAULT NULL, ADD position VARCHAR(100) DEFAULT NULL, ADD nom_province VARCHAR(100) DEFAULT NULL, ADD nom_compagnon VARCHAR(100) DEFAULT NULL, ADD telephone VARCHAR(20) DEFAULT NULL, ADD fonction1 VARCHAR(100) DEFAULT NULL, ADD fonction2 VARCHAR(100) DEFAULT NULL, ADD adresse1 VARCHAR(255) DEFAULT NULL, ADD adresse2 VARCHAR(255) DEFAULT NULL, ADD code_postale VARCHAR(10) DEFAULT NULL, ADD ville VARCHAR(100) DEFAULT NULL, ADD droit VARCHAR(100) DEFAULT NULL, ADD photo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP prenom, DROP nom, DROP metier, DROP statut, DROP position, DROP nom_province, DROP nom_compagnon, DROP telephone, DROP fonction1, DROP fonction2, DROP adresse1, DROP adresse2, DROP code_postale, DROP ville, DROP droit, DROP photo');
    }
}
