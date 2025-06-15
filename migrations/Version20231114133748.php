<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231114133748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('BEGIN;');
        $this->addSql('LOCK TABLE messenger_messages;');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('COMMIT;');
        $this->addSql('ALTER TABLE answer ADD client_comment TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD score_video DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD magnitude_video DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE scenario DROP CONSTRAINT FK_3E45C8D86F6FCB26');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT FK_3E45C8D86F6FCB26 FOREIGN KEY (panel_id) REFERENCES panel (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE scenario DROP CONSTRAINT fk_3e45c8d86f6fcb26');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT fk_3e45c8d86f6fcb26 FOREIGN KEY (panel_id) REFERENCES panel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer DROP client_comment');
        $this->addSql('ALTER TABLE answer DROP score_video');
        $this->addSql('ALTER TABLE answer DROP magnitude_video');
    }
}
