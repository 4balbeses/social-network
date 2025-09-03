<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901164100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add profile_image_id column to artist table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist ADD COLUMN profile_image_id INTEGER DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_1599687C98C6BB35 ON artist (profile_image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_1599687C98C6BB35');
        $this->addSql('CREATE TEMPORARY TABLE __temp__artist AS SELECT id, full_name, description FROM artist');
        $this->addSql('DROP TABLE artist');
        $this->addSql('CREATE TABLE artist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO artist (id, full_name, description) SELECT id, full_name, description FROM __temp__artist');
        $this->addSql('DROP TABLE __temp__artist');
    }
}