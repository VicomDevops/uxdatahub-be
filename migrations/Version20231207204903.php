<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231207204903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ADD client_tester_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A257CBAF1CE FOREIGN KEY (client_tester_id) REFERENCES client_tester (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DADD4A257CBAF1CE ON answer (client_tester_id)');
        $this->addSql('DROP INDEX uniq_e98f285956e1b8c8');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A257CBAF1CE');
        $this->addSql('DROP INDEX IDX_DADD4A257CBAF1CE');
        $this->addSql('ALTER TABLE answer DROP client_tester_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_e98f285956e1b8c8 ON contract (licence_category_id)');
    }
}
