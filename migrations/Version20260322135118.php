<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322135118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_draft (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, version_number INTEGER NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, project_id INTEGER NOT NULL, CONSTRAINT FK_ADBFC246166D1F9C FOREIGN KEY (project_id) REFERENCES content_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_ADBFC246166D1F9C ON content_draft (project_id)');
        $this->addSql('CREATE TABLE content_project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, initial_brief CLOB NOT NULL, target_audience VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE review_feedback (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, critique CLOB NOT NULL, suggestions CLOB DEFAULT NULL, is_approved BOOLEAN NOT NULL, created_at DATETIME NOT NULL, draft_id INTEGER NOT NULL, CONSTRAINT FK_F16BCD53E2F3C5D1 FOREIGN KEY (draft_id) REFERENCES content_draft (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F16BCD53E2F3C5D1 ON review_feedback (draft_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE content_draft');
        $this->addSql('DROP TABLE content_project');
        $this->addSql('DROP TABLE review_feedback');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
