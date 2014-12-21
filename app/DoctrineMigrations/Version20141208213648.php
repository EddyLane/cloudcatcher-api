<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141208213648 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('CREATE SEQUENCE fridge_subscription_card_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_subscription_stripe_profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_user_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_user_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_user_preference_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_api_access_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_api_auth_code_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_api_client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_api_gcm_id_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fridge_api_refresh_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE Podcast_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fridge_subscription_card (id INT NOT NULL, stripe_profile_id INT DEFAULT NULL, number VARCHAR(4) NOT NULL, card_type SMALLINT NOT NULL, exp_month INT NOT NULL, exp_year INT NOT NULL, token VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC87F38CB7F4B430 ON fridge_subscription_card (stripe_profile_id)');
        $this->addSql('CREATE TABLE fridge_subscription_stripe_profile (id INT NOT NULL, subscription_id INT DEFAULT NULL, default_card_id INT DEFAULT NULL, stripe_id VARCHAR(255) DEFAULT NULL, subscription_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, subscription_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1DB3FAB59A1887DC ON fridge_subscription_stripe_profile (subscription_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DB3FAB5B849CB65 ON fridge_subscription_stripe_profile (default_card_id)');
        $this->addSql('CREATE TABLE fridge_subscription (id INT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 0) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_386F5E1D5E237E06 ON fridge_subscription (name)');
        $this->addSql('CREATE TABLE fridge_user_user (id INT NOT NULL, stripe_profile_id INT DEFAULT NULL, user_preference_id INT DEFAULT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, expired BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles TEXT NOT NULL, credentials_expired BOOLEAN NOT NULL, credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E03DE8BC92FC23A8 ON fridge_user_user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E03DE8BCA0D96FBF ON fridge_user_user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E03DE8BCB7F4B430 ON fridge_user_user (stripe_profile_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E03DE8BC369E8F90 ON fridge_user_user (user_preference_id)');
        $this->addSql('COMMENT ON COLUMN fridge_user_user.roles IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE fridge_user_user_group (user_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(user_id, group_id))');
        $this->addSql('CREATE INDEX IDX_39F70795A76ED395 ON fridge_user_user_group (user_id)');
        $this->addSql('CREATE INDEX IDX_39F70795FE54D947 ON fridge_user_user_group (group_id)');
        $this->addSql('CREATE TABLE fridge_user_group (id INT NOT NULL, name VARCHAR(255) NOT NULL, roles TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A07AEC685E237E06 ON fridge_user_group (name)');
        $this->addSql('COMMENT ON COLUMN fridge_user_group.roles IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE fridge_user_preference (id INT NOT NULL, limitEpisodes SMALLINT NOT NULL, downloadEpisodes BOOLEAN NOT NULL, deletePlayedEpisodes BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE fridge_api_access_token (id INT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BBEB0DA5F37A13B ON fridge_api_access_token (token)');
        $this->addSql('CREATE INDEX IDX_1BBEB0DA19EB6921 ON fridge_api_access_token (client_id)');
        $this->addSql('CREATE INDEX IDX_1BBEB0DAA76ED395 ON fridge_api_access_token (user_id)');
        $this->addSql('CREATE TABLE fridge_api_auth_code (id INT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri TEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_58A033DA5F37A13B ON fridge_api_auth_code (token)');
        $this->addSql('CREATE INDEX IDX_58A033DA19EB6921 ON fridge_api_auth_code (client_id)');
        $this->addSql('CREATE INDEX IDX_58A033DAA76ED395 ON fridge_api_auth_code (user_id)');
        $this->addSql('CREATE TABLE fridge_api_client (id INT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris TEXT NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN fridge_api_client.redirect_uris IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN fridge_api_client.allowed_grant_types IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE fridge_api_gcm_id (id INT NOT NULL, user_id INT DEFAULT NULL, gcm_id VARCHAR(1024) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8362B455A76ED395 ON fridge_api_gcm_id (user_id)');
        $this->addSql('CREATE TABLE fridge_api_refresh_token (id INT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E28DEF585F37A13B ON fridge_api_refresh_token (token)');
        $this->addSql('CREATE INDEX IDX_E28DEF5819EB6921 ON fridge_api_refresh_token (client_id)');
        $this->addSql('CREATE INDEX IDX_E28DEF58A76ED395 ON fridge_api_refresh_token (user_id)');
        $this->addSql('CREATE TABLE Podcast (id INT NOT NULL, amount INT NOT NULL, feed VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, image_url_30 VARCHAR(255) NOT NULL, image_url_100 VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, explicit BOOLEAN NOT NULL, genres TEXT NOT NULL, heard TEXT NOT NULL, itunes_id INT NOT NULL, latest VARCHAR(255) NOT NULL, latest_episode TEXT NOT NULL, name VARCHAR(255) NOT NULL, new_episodes INT NOT NULL, slug VARCHAR(255) NOT NULL, auto_download INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN Podcast.genres IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN Podcast.heard IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN Podcast.latest_episode IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE fridge_subscription_card ADD CONSTRAINT FK_AC87F38CB7F4B430 FOREIGN KEY (stripe_profile_id) REFERENCES fridge_subscription_stripe_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_subscription_stripe_profile ADD CONSTRAINT FK_1DB3FAB59A1887DC FOREIGN KEY (subscription_id) REFERENCES fridge_subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_subscription_stripe_profile ADD CONSTRAINT FK_1DB3FAB5B849CB65 FOREIGN KEY (default_card_id) REFERENCES fridge_subscription_card (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_user_user ADD CONSTRAINT FK_E03DE8BCB7F4B430 FOREIGN KEY (stripe_profile_id) REFERENCES fridge_subscription_stripe_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_user_user ADD CONSTRAINT FK_E03DE8BC369E8F90 FOREIGN KEY (user_preference_id) REFERENCES fridge_user_preference (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_user_user_group ADD CONSTRAINT FK_39F70795A76ED395 FOREIGN KEY (user_id) REFERENCES fridge_user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_user_user_group ADD CONSTRAINT FK_39F70795FE54D947 FOREIGN KEY (group_id) REFERENCES fridge_user_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_access_token ADD CONSTRAINT FK_1BBEB0DA19EB6921 FOREIGN KEY (client_id) REFERENCES fridge_api_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_access_token ADD CONSTRAINT FK_1BBEB0DAA76ED395 FOREIGN KEY (user_id) REFERENCES fridge_user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_auth_code ADD CONSTRAINT FK_58A033DA19EB6921 FOREIGN KEY (client_id) REFERENCES fridge_api_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_auth_code ADD CONSTRAINT FK_58A033DAA76ED395 FOREIGN KEY (user_id) REFERENCES fridge_user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_gcm_id ADD CONSTRAINT FK_8362B455A76ED395 FOREIGN KEY (user_id) REFERENCES fridge_user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_refresh_token ADD CONSTRAINT FK_E28DEF5819EB6921 FOREIGN KEY (client_id) REFERENCES fridge_api_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fridge_api_refresh_token ADD CONSTRAINT FK_E28DEF58A76ED395 FOREIGN KEY (user_id) REFERENCES fridge_user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE fridge_subscription_stripe_profile DROP CONSTRAINT FK_1DB3FAB5B849CB65');
        $this->addSql('ALTER TABLE fridge_subscription_card DROP CONSTRAINT FK_AC87F38CB7F4B430');
        $this->addSql('ALTER TABLE fridge_user_user DROP CONSTRAINT FK_E03DE8BCB7F4B430');
        $this->addSql('ALTER TABLE fridge_subscription_stripe_profile DROP CONSTRAINT FK_1DB3FAB59A1887DC');
        $this->addSql('ALTER TABLE fridge_user_user_group DROP CONSTRAINT FK_39F70795A76ED395');
        $this->addSql('ALTER TABLE fridge_api_access_token DROP CONSTRAINT FK_1BBEB0DAA76ED395');
        $this->addSql('ALTER TABLE fridge_api_auth_code DROP CONSTRAINT FK_58A033DAA76ED395');
        $this->addSql('ALTER TABLE fridge_api_gcm_id DROP CONSTRAINT FK_8362B455A76ED395');
        $this->addSql('ALTER TABLE fridge_api_refresh_token DROP CONSTRAINT FK_E28DEF58A76ED395');
        $this->addSql('ALTER TABLE fridge_user_user_group DROP CONSTRAINT FK_39F70795FE54D947');
        $this->addSql('ALTER TABLE fridge_user_user DROP CONSTRAINT FK_E03DE8BC369E8F90');
        $this->addSql('ALTER TABLE fridge_api_access_token DROP CONSTRAINT FK_1BBEB0DA19EB6921');
        $this->addSql('ALTER TABLE fridge_api_auth_code DROP CONSTRAINT FK_58A033DA19EB6921');
        $this->addSql('ALTER TABLE fridge_api_refresh_token DROP CONSTRAINT FK_E28DEF5819EB6921');
        $this->addSql('DROP SEQUENCE fridge_subscription_card_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_subscription_stripe_profile_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_user_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_user_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_user_preference_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_api_access_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_api_auth_code_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_api_client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_api_gcm_id_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fridge_api_refresh_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE Podcast_id_seq CASCADE');
        $this->addSql('DROP TABLE fridge_subscription_card');
        $this->addSql('DROP TABLE fridge_subscription_stripe_profile');
        $this->addSql('DROP TABLE fridge_subscription');
        $this->addSql('DROP TABLE fridge_user_user');
        $this->addSql('DROP TABLE fridge_user_user_group');
        $this->addSql('DROP TABLE fridge_user_group');
        $this->addSql('DROP TABLE fridge_user_preference');
        $this->addSql('DROP TABLE fridge_api_access_token');
        $this->addSql('DROP TABLE fridge_api_auth_code');
        $this->addSql('DROP TABLE fridge_api_client');
        $this->addSql('DROP TABLE fridge_api_gcm_id');
        $this->addSql('DROP TABLE fridge_api_refresh_token');
        $this->addSql('DROP TABLE Podcast');
    }
}
