<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903163915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, founder_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, industry VARCHAR(100) DEFAULT NULL, stage VARCHAR(50) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, founded_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , valuation BIGINT DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_4FBF094F19113B3C FOREIGN KEY (founder_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4FBF094F19113B3C ON company (founder_id)');
        $this->addSql('CREATE TABLE investment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, investor_id INTEGER NOT NULL, company_id INTEGER NOT NULL, pitch_id INTEGER DEFAULT NULL, amount BIGINT NOT NULL, investment_type VARCHAR(50) DEFAULT NULL, equity_percentage NUMERIC(5, 2) DEFAULT NULL, status VARCHAR(50) NOT NULL, terms CLOB DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_43CA0AD69AE528DA FOREIGN KEY (investor_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_43CA0AD6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_43CA0AD6FEEFC64B FOREIGN KEY (pitch_id) REFERENCES pitch (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_43CA0AD69AE528DA ON investment (investor_id)');
        $this->addSql('CREATE INDEX IDX_43CA0AD6979B1AD6 ON investment (company_id)');
        $this->addSql('CREATE INDEX IDX_43CA0AD6FEEFC64B ON investment (pitch_id)');
        $this->addSql('CREATE TABLE pitch (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, company_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, description CLOB NOT NULL, funding_goal BIGINT DEFAULT NULL, current_funding BIGINT DEFAULT NULL, deck_url VARCHAR(255) DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, is_active BOOLEAN NOT NULL, deadline DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_279FBED9979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_279FBED9979B1AD6 ON pitch (company_id)');
        $this->addSql('CREATE TABLE pitch_comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, pitch_id INTEGER NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_16F5BD59F675F31B FOREIGN KEY (author_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_16F5BD59FEEFC64B FOREIGN KEY (pitch_id) REFERENCES pitch (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_16F5BD59F675F31B ON pitch_comment (author_id)');
        $this->addSql('CREATE INDEX IDX_16F5BD59FEEFC64B ON pitch_comment (pitch_id)');
        $this->addSql('CREATE TABLE user_company (user_id INTEGER NOT NULL, company_id INTEGER NOT NULL, PRIMARY KEY(user_id, company_id), CONSTRAINT FK_17B21745A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_17B21745979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_17B21745A76ED395 ON user_company (user_id)');
        $this->addSql('CREATE INDEX IDX_17B21745979B1AD6 ON user_company (company_id)');
        $this->addSql('CREATE TABLE user_pitch (user_id INTEGER NOT NULL, pitch_id INTEGER NOT NULL, PRIMARY KEY(user_id, pitch_id), CONSTRAINT FK_C55D4581A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C55D4581FEEFC64B FOREIGN KEY (pitch_id) REFERENCES pitch (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C55D4581A76ED395 ON user_pitch (user_id)');
        $this->addSql('CREATE INDEX IDX_C55D4581FEEFC64B ON user_pitch (pitch_id)');
        $this->addSql('CREATE TABLE user_followers (user_source INTEGER NOT NULL, user_target INTEGER NOT NULL, PRIMARY KEY(user_source, user_target), CONSTRAINT FK_84E870433AD8644E FOREIGN KEY (user_source) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_84E87043233D34C1 FOREIGN KEY (user_target) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_84E870433AD8644E ON user_followers (user_source)');
        $this->addSql('CREATE INDEX IDX_84E87043233D34C1 ON user_followers (user_target)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__artist AS SELECT id, full_name, description, profile_image_id FROM artist');
        $this->addSql('DROP TABLE artist');
        $this->addSql('CREATE TABLE artist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, profile_image_id INTEGER DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, CONSTRAINT FK_1599687C4CF44DC FOREIGN KEY (profile_image_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO artist (id, full_name, description, profile_image_id) SELECT id, full_name, description, profile_image_id FROM __temp__artist');
        $this->addSql('DROP TABLE __temp__artist');
        $this->addSql('CREATE INDEX IDX_1599687C4CF44DC ON artist (profile_image_id)');
        $this->addSql('ALTER TABLE user ADD COLUMN user_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN bio CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN linkedin VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN twitter VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN industry VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN expertise VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE investment');
        $this->addSql('DROP TABLE pitch');
        $this->addSql('DROP TABLE pitch_comment');
        $this->addSql('DROP TABLE user_company');
        $this->addSql('DROP TABLE user_pitch');
        $this->addSql('DROP TABLE user_followers');
        $this->addSql('CREATE TEMPORARY TABLE __temp__artist AS SELECT id, profile_image_id, full_name, description FROM artist');
        $this->addSql('DROP TABLE artist');
        $this->addSql('CREATE TABLE artist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, profile_image_id INTEGER DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO artist (id, profile_image_id, full_name, description) SELECT id, profile_image_id, full_name, description FROM __temp__artist');
        $this->addSql('DROP TABLE __temp__artist');
        $this->addSql('CREATE INDEX IDX_1599687C98C6BB35 ON artist (profile_image_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, password, registered_at, full_name, roles FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL, full_name VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO user (id, username, password, registered_at, full_name, roles) SELECT id, username, password, registered_at, full_name, roles FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }
}
