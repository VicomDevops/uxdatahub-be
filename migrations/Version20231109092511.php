<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231109092511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contract_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE credit_pack_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE face_shot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE help_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE licence_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE licence_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE panel_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE question_choices_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE salience_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE scenario_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sentence_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE step_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE test_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE admin (id INT NOT NULL, name VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE answer (id INT NOT NULL, step_id INT NOT NULL, test_id INT NOT NULL, answer VARCHAR(255) NOT NULL, comment VARCHAR(255) DEFAULT NULL, video_text TEXT DEFAULT NULL, magnitude DOUBLE PRECISION DEFAULT NULL, score DOUBLE PRECISION DEFAULT NULL, start_at TIME(0) WITHOUT TIME ZONE DEFAULT NULL, end_at TIME(0) WITHOUT TIME ZONE DEFAULT NULL, joy DOUBLE PRECISION DEFAULT NULL, sorrow DOUBLE PRECISION DEFAULT NULL, anger DOUBLE PRECISION DEFAULT NULL, surprise DOUBLE PRECISION DEFAULT NULL, confidence DOUBLE PRECISION DEFAULT NULL, video VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DADD4A2573B21E9C ON answer (step_id)');
        $this->addSql('CREATE INDEX IDX_DADD4A251E5D0459 ON answer (test_id)');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, name VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, phone VARCHAR(30) NOT NULL, company VARCHAR(255) NOT NULL, profession VARCHAR(255) NOT NULL, sector VARCHAR(255) NOT NULL, nb_employees VARCHAR(15) NOT NULL, use_case VARCHAR(255) NOT NULL, stripe_id VARCHAR(255) DEFAULT NULL, contract_link VARCHAR(255) DEFAULT NULL, privacy_policy BOOLEAN DEFAULT NULL, cgu BOOLEAN DEFAULT NULL, profile_image VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404553F1B1098 ON client (stripe_id)');
        $this->addSql('CREATE TABLE client_tester (id INT NOT NULL, name VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, gender VARCHAR(20) DEFAULT NULL, country VARCHAR(50) DEFAULT NULL, csp VARCHAR(255) DEFAULT NULL, os VARCHAR(100) DEFAULT NULL, date_of_birth DATE DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, os_mobile VARCHAR(100) DEFAULT NULL, os_tablet VARCHAR(100) DEFAULT NULL, social_media VARCHAR(100) DEFAULT NULL, marital_status VARCHAR(50) DEFAULT NULL, study_level VARCHAR(50) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, profile_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE client_tester_panel (client_tester_id INT NOT NULL, panel_id INT NOT NULL, PRIMARY KEY(client_tester_id, panel_id))');
        $this->addSql('CREATE INDEX IDX_68AB9307CBAF1CE ON client_tester_panel (client_tester_id)');
        $this->addSql('CREATE INDEX IDX_68AB9306F6FCB26 ON client_tester_panel (panel_id)');
        $this->addSql('CREATE TABLE comment (id INT NOT NULL, client_id INT NOT NULL, answer_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9474526C19EB6921 ON comment (client_id)');
        $this->addSql('CREATE INDEX IDX_9474526CAA334807 ON comment (answer_id)');
        $this->addSql('CREATE TABLE contract (id INT NOT NULL, client_id INT NOT NULL, licence_category_id INT NOT NULL, company_to_invoice VARCHAR(255) DEFAULT NULL, signing_name VARCHAR(255) DEFAULT NULL, first_name_signatory VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, invoice_email VARCHAR(255) DEFAULT NULL, country_residence VARCHAR(255) DEFAULT NULL, identity_card_front VARCHAR(255) DEFAULT NULL, identity_card_back VARCHAR(255) DEFAULT NULL, num_voie VARCHAR(255) DEFAULT NULL, account_owner VARCHAR(255) DEFAULT NULL, bank_name VARCHAR(255) DEFAULT NULL, iban VARCHAR(255) DEFAULT NULL, code_bic VARCHAR(255) DEFAULT NULL, status INT DEFAULT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E98F285919EB6921 ON contract (client_id)');
        $this->addSql('CREATE INDEX IDX_E98F285956E1B8C8 ON contract (licence_category_id)');
        $this->addSql('CREATE TABLE credit_pack (id INT NOT NULL, name VARCHAR(100) NOT NULL, credits SMALLINT NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE face_shot (id INT NOT NULL, answer_id INT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7568B89AA334807 ON face_shot (answer_id)');
        $this->addSql('CREATE TABLE help (id INT NOT NULL, launcher_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, commentaire TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8875CAC2724B909 ON help (launcher_id)');
        $this->addSql('CREATE TABLE licence (id INT NOT NULL, client_id INT NOT NULL, licence_category_id INT NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DAAE64819EB6921 ON licence (client_id)');
        $this->addSql('CREATE INDEX IDX_1DAAE64856E1B8C8 ON licence (licence_category_id)');
        $this->addSql('CREATE TABLE licence_category (id INT NOT NULL, title VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, is_multi_platform BOOLEAN NOT NULL, is_insight_panel BOOLEAN NOT NULL, is_moderate_test BOOLEAN NOT NULL, is_statistics BOOLEAN NOT NULL, is_stat_by_step_and_by_tester BOOLEAN NOT NULL, is_deep BOOLEAN NOT NULL, is_non_moderate_test BOOLEAN NOT NULL, is_product_service_scenario BOOLEAN NOT NULL, is_ab_test BOOLEAN NOT NULL, is_speech_to_text BOOLEAN NOT NULL, is_journey_map BOOLEAN NOT NULL, is_emptional_map BOOLEAN NOT NULL, is_profile_types BOOLEAN NOT NULL, sub_clients_nb INT DEFAULT NULL, testers_nb INT DEFAULT NULL, client_testers_nb INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE panel (id INT NOT NULL, name VARCHAR(255) NOT NULL, scenario_name VARCHAR(255) NOT NULL, testers_nb SMALLINT NOT NULL, product VARCHAR(100) DEFAULT NULL, gender VARCHAR(20) DEFAULT NULL, csp VARCHAR(255) DEFAULT NULL, os VARCHAR(255) DEFAULT NULL, study_level VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, marital_status VARCHAR(255) DEFAULT NULL, min_age SMALLINT DEFAULT NULL, max_age SMALLINT DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE question_choices (id INT NOT NULL, choice1 VARCHAR(255) DEFAULT NULL, choice2 VARCHAR(255) DEFAULT NULL, choice3 VARCHAR(255) DEFAULT NULL, choice4 VARCHAR(255) DEFAULT NULL, choice5 VARCHAR(255) DEFAULT NULL, choice6 VARCHAR(255) DEFAULT NULL, min_scale SMALLINT DEFAULT NULL, max_scale SMALLINT DEFAULT NULL, borne_inf VARCHAR(255) DEFAULT NULL, borne_sup VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE salience (id INT NOT NULL, answer_id INT NOT NULL, word VARCHAR(100) NOT NULL, salience DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BC9198FEAA334807 ON salience (answer_id)');
        $this->addSql('CREATE TABLE scenario (id INT NOT NULL, client_id INT NOT NULL, panel_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, product VARCHAR(25) NOT NULL, is_unique BOOLEAN NOT NULL, is_moderate BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, langue VARCHAR(25) DEFAULT NULL, validate BOOLEAN DEFAULT NULL, etat INT DEFAULT 0, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_tested INT DEFAULT 0, progress INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3E45C8D819EB6921 ON scenario (client_id)');
        $this->addSql('CREATE INDEX IDX_3E45C8D86F6FCB26 ON scenario (panel_id)');
        $this->addSql('CREATE TABLE sentence (id INT NOT NULL, answer_id INT DEFAULT NULL, content TEXT NOT NULL, magnitude DOUBLE PRECISION NOT NULL, score DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9D664ED5AA334807 ON sentence (answer_id)');
        $this->addSql('CREATE TABLE step (id INT NOT NULL, scenario_id INT NOT NULL, question_choices_id INT DEFAULT NULL, url TEXT DEFAULT NULL, instruction TEXT DEFAULT NULL, question TEXT NOT NULL, number SMALLINT NOT NULL, type VARCHAR(100) DEFAULT NULL, average DOUBLE PRECISION DEFAULT NULL, deviation DOUBLE PRECISION DEFAULT NULL, joy DOUBLE PRECISION DEFAULT NULL, sorrow DOUBLE PRECISION DEFAULT NULL, anger DOUBLE PRECISION DEFAULT NULL, surprise DOUBLE PRECISION DEFAULT NULL, confidence DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_43B9FE3CE04E49DF ON step (scenario_id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3C71E84689 ON step (question_choices_id)');
        $this->addSql('CREATE TABLE sub_client (id INT NOT NULL, client_id INT NOT NULL, name VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, write_rights BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75154B1919EB6921 ON sub_client (client_id)');
        $this->addSql('CREATE TABLE test (id INT NOT NULL, scenario_id INT NOT NULL, client_tester_id INT DEFAULT NULL, tester_id INT DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, video VARCHAR(255) DEFAULT NULL, state VARCHAR(50) DEFAULT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, etat INT DEFAULT 0, average DOUBLE PRECISION DEFAULT NULL, is_analyzed BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D87F7E0CE04E49DF ON test (scenario_id)');
        $this->addSql('CREATE INDEX IDX_D87F7E0C7CBAF1CE ON test (client_tester_id)');
        $this->addSql('CREATE INDEX IDX_D87F7E0C979A21C1 ON test (tester_id)');
        $this->addSql('CREATE TABLE tester (id INT NOT NULL, name VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, gender VARCHAR(20) NOT NULL, country VARCHAR(50) NOT NULL, csp VARCHAR(255) NOT NULL, study_level VARCHAR(50) NOT NULL, activity_area VARCHAR(50) DEFAULT NULL, department VARCHAR(50) DEFAULT NULL, revenu VARCHAR(50) DEFAULT NULL, poste VARCHAR(100) DEFAULT NULL, fonction_manageriale VARCHAR(50) DEFAULT NULL, taille_ste VARCHAR(50) DEFAULT NULL, social_media VARCHAR(100) NOT NULL, os VARCHAR(100) NOT NULL, os_mobile VARCHAR(100) DEFAULT NULL, os_tablet VARCHAR(100) DEFAULT NULL, marital_status VARCHAR(50) NOT NULL, date_of_birth DATE NOT NULL, phone VARCHAR(30) NOT NULL, postal_code INT DEFAULT NULL, langue VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, internet_frequency VARCHAR(50) DEFAULT NULL, achat_internet INT DEFAULT NULL, stripe_id VARCHAR(255) DEFAULT NULL, temps_passe VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, identity_card_front VARCHAR(255) DEFAULT NULL, identity_card_back VARCHAR(255) DEFAULT NULL, privacy_policy VARCHAR(255) DEFAULT NULL, cgu VARCHAR(255) DEFAULT NULL, device VARCHAR(255) DEFAULT NULL, profile_informations BOOLEAN DEFAULT NULL, test_visio BOOLEAN DEFAULT NULL, via_email BOOLEAN DEFAULT NULL, sms BOOLEAN DEFAULT NULL, completion_rate INT DEFAULT NULL, profile_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tester_panel (tester_id INT NOT NULL, panel_id INT NOT NULL, PRIMARY KEY(tester_id, panel_id))');
        $this->addSql('CREATE INDEX IDX_B7801B31979A21C1 ON tester_panel (tester_id)');
        $this->addSql('CREATE INDEX IDX_B7801B316F6FCB26 ON tester_panel (panel_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, is_active BOOLEAN NOT NULL, is_first_connection BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, state VARCHAR(50) DEFAULT NULL, username VARCHAR(100) DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE admin ADD CONSTRAINT FK_880E0D76BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A2573B21E9C FOREIGN KEY (step_id) REFERENCES step (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_tester ADD CONSTRAINT FK_A9F61DF6BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_tester_panel ADD CONSTRAINT FK_68AB9307CBAF1CE FOREIGN KEY (client_tester_id) REFERENCES client_tester (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_tester_panel ADD CONSTRAINT FK_68AB9306F6FCB26 FOREIGN KEY (panel_id) REFERENCES panel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CAA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285956E1B8C8 FOREIGN KEY (licence_category_id) REFERENCES licence_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE face_shot ADD CONSTRAINT FK_7568B89AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE help ADD CONSTRAINT FK_8875CAC2724B909 FOREIGN KEY (launcher_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE licence ADD CONSTRAINT FK_1DAAE64819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE licence ADD CONSTRAINT FK_1DAAE64856E1B8C8 FOREIGN KEY (licence_category_id) REFERENCES licence_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE salience ADD CONSTRAINT FK_BC9198FEAA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT FK_3E45C8D819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT FK_3E45C8D86F6FCB26 FOREIGN KEY (panel_id) REFERENCES panel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sentence ADD CONSTRAINT FK_9D664ED5AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3CE04E49DF FOREIGN KEY (scenario_id) REFERENCES scenario (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C71E84689 FOREIGN KEY (question_choices_id) REFERENCES question_choices (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sub_client ADD CONSTRAINT FK_75154B1919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sub_client ADD CONSTRAINT FK_75154B19BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0CE04E49DF FOREIGN KEY (scenario_id) REFERENCES scenario (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C7CBAF1CE FOREIGN KEY (client_tester_id) REFERENCES client_tester (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C979A21C1 FOREIGN KEY (tester_id) REFERENCES tester (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tester ADD CONSTRAINT FK_FC505645BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tester_panel ADD CONSTRAINT FK_B7801B31979A21C1 FOREIGN KEY (tester_id) REFERENCES tester (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tester_panel ADD CONSTRAINT FK_B7801B316F6FCB26 FOREIGN KEY (panel_id) REFERENCES panel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE answer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE comment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE contract_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE credit_pack_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE face_shot_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE help_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE licence_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE licence_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE panel_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE question_choices_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE salience_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE scenario_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sentence_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE step_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE test_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE admin DROP CONSTRAINT FK_880E0D76BF396750');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A2573B21E9C');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E5D0459');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455BF396750');
        $this->addSql('ALTER TABLE client_tester DROP CONSTRAINT FK_A9F61DF6BF396750');
        $this->addSql('ALTER TABLE client_tester_panel DROP CONSTRAINT FK_68AB9307CBAF1CE');
        $this->addSql('ALTER TABLE client_tester_panel DROP CONSTRAINT FK_68AB9306F6FCB26');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C19EB6921');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526CAA334807');
        $this->addSql('ALTER TABLE contract DROP CONSTRAINT FK_E98F285919EB6921');
        $this->addSql('ALTER TABLE contract DROP CONSTRAINT FK_E98F285956E1B8C8');
        $this->addSql('ALTER TABLE face_shot DROP CONSTRAINT FK_7568B89AA334807');
        $this->addSql('ALTER TABLE help DROP CONSTRAINT FK_8875CAC2724B909');
        $this->addSql('ALTER TABLE licence DROP CONSTRAINT FK_1DAAE64819EB6921');
        $this->addSql('ALTER TABLE licence DROP CONSTRAINT FK_1DAAE64856E1B8C8');
        $this->addSql('ALTER TABLE salience DROP CONSTRAINT FK_BC9198FEAA334807');
        $this->addSql('ALTER TABLE scenario DROP CONSTRAINT FK_3E45C8D819EB6921');
        $this->addSql('ALTER TABLE scenario DROP CONSTRAINT FK_3E45C8D86F6FCB26');
        $this->addSql('ALTER TABLE sentence DROP CONSTRAINT FK_9D664ED5AA334807');
        $this->addSql('ALTER TABLE step DROP CONSTRAINT FK_43B9FE3CE04E49DF');
        $this->addSql('ALTER TABLE step DROP CONSTRAINT FK_43B9FE3C71E84689');
        $this->addSql('ALTER TABLE sub_client DROP CONSTRAINT FK_75154B1919EB6921');
        $this->addSql('ALTER TABLE sub_client DROP CONSTRAINT FK_75154B19BF396750');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0CE04E49DF');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C7CBAF1CE');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C979A21C1');
        $this->addSql('ALTER TABLE tester DROP CONSTRAINT FK_FC505645BF396750');
        $this->addSql('ALTER TABLE tester_panel DROP CONSTRAINT FK_B7801B31979A21C1');
        $this->addSql('ALTER TABLE tester_panel DROP CONSTRAINT FK_B7801B316F6FCB26');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_tester');
        $this->addSql('DROP TABLE client_tester_panel');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE credit_pack');
        $this->addSql('DROP TABLE face_shot');
        $this->addSql('DROP TABLE help');
        $this->addSql('DROP TABLE licence');
        $this->addSql('DROP TABLE licence_category');
        $this->addSql('DROP TABLE panel');
        $this->addSql('DROP TABLE question_choices');
        $this->addSql('DROP TABLE salience');
        $this->addSql('DROP TABLE scenario');
        $this->addSql('DROP TABLE sentence');
        $this->addSql('DROP TABLE step');
        $this->addSql('DROP TABLE sub_client');
        $this->addSql('DROP TABLE test');
        $this->addSql('DROP TABLE tester');
        $this->addSql('DROP TABLE tester_panel');
        $this->addSql('DROP TABLE "user"');
    }
}
