<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413175216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for OnboardHub MVP';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX uq_company_slug (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE onboarding (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, due_date DATE DEFAULT NULL, status VARCHAR(255) NOT NULL, progress_percent SMALLINT UNSIGNED NOT NULL, template_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_23A7BB0EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE onboarding_software (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, install_url VARCHAR(2048) DEFAULT NULL, status VARCHAR(255) NOT NULL, completed_at DATETIME DEFAULT NULL, position INT UNSIGNED NOT NULL, onboarding_id INT NOT NULL, INDEX IDX_197AA6B4235CA921 (onboarding_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE onboarding_task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, priority SMALLINT UNSIGNED NOT NULL, due_date DATE DEFAULT NULL, completed_at DATETIME DEFAULT NULL, position INT UNSIGNED NOT NULL, onboarding_id INT NOT NULL, assigned_by_id INT DEFAULT NULL, INDEX IDX_1886E040235CA921 (onboarding_id), INDEX IDX_1886E0406E6F1246 (assigned_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE onboarding_training (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(2048) DEFAULT NULL, status VARCHAR(255) NOT NULL, completed_at DATETIME DEFAULT NULL, position INT UNSIGNED NOT NULL, onboarding_id INT NOT NULL, INDEX IDX_BBB844F4235CA921 (onboarding_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, company_id INT NOT NULL, INDEX IDX_C4E0A61F979B1AD6 (company_id), UNIQUE INDEX uq_team_company_name (company_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tech_article (id INT AUTO_INCREMENT NOT NULL, source VARCHAR(32) NOT NULL, external_id VARCHAR(64) NOT NULL, title VARCHAR(512) NOT NULL, url VARCHAR(2048) NOT NULL, author VARCHAR(255) DEFAULT NULL, score INT DEFAULT NULL, published_at DATETIME DEFAULT NULL, fetched_at DATETIME NOT NULL, UNIQUE INDEX uq_article_source_external (source, external_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_tech_state (id INT AUTO_INCREMENT NOT NULL, is_read TINYINT NOT NULL, read_at DATETIME DEFAULT NULL, is_saved TINYINT NOT NULL, saved_at DATETIME DEFAULT NULL, user_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_D3EF758DA76ED395 (user_id), INDEX IDX_D3EF758D7294869C (article_id), UNIQUE INDEX uq_state_user_article (user_id, article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120) NOT NULL, role VARCHAR(255) NOT NULL, provider VARCHAR(50) NOT NULL, external_id VARCHAR(128) NOT NULL, hired_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, company_id INT NOT NULL, team_id INT DEFAULT NULL, manager_id INT DEFAULT NULL, INDEX IDX_1483A5E9979B1AD6 (company_id), INDEX IDX_1483A5E9296CD8AE (team_id), INDEX IDX_1483A5E9783E3463 (manager_id), UNIQUE INDEX uq_users_company_email (company_id, email), UNIQUE INDEX uq_users_provider_external (provider, external_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE onboarding ADD CONSTRAINT FK_23A7BB0EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onboarding_software ADD CONSTRAINT FK_197AA6B4235CA921 FOREIGN KEY (onboarding_id) REFERENCES onboarding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onboarding_task ADD CONSTRAINT FK_1886E040235CA921 FOREIGN KEY (onboarding_id) REFERENCES onboarding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onboarding_task ADD CONSTRAINT FK_1886E0406E6F1246 FOREIGN KEY (assigned_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE onboarding_training ADD CONSTRAINT FK_BBB844F4235CA921 FOREIGN KEY (onboarding_id) REFERENCES onboarding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_tech_state ADD CONSTRAINT FK_D3EF758DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_tech_state ADD CONSTRAINT FK_D3EF758D7294869C FOREIGN KEY (article_id) REFERENCES tech_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9783E3463 FOREIGN KEY (manager_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE onboarding DROP FOREIGN KEY FK_23A7BB0EA76ED395');
        $this->addSql('ALTER TABLE onboarding_software DROP FOREIGN KEY FK_197AA6B4235CA921');
        $this->addSql('ALTER TABLE onboarding_task DROP FOREIGN KEY FK_1886E040235CA921');
        $this->addSql('ALTER TABLE onboarding_task DROP FOREIGN KEY FK_1886E0406E6F1246');
        $this->addSql('ALTER TABLE onboarding_training DROP FOREIGN KEY FK_BBB844F4235CA921');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F979B1AD6');
        $this->addSql('ALTER TABLE user_tech_state DROP FOREIGN KEY FK_D3EF758DA76ED395');
        $this->addSql('ALTER TABLE user_tech_state DROP FOREIGN KEY FK_D3EF758D7294869C');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9979B1AD6');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9296CD8AE');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9783E3463');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE onboarding');
        $this->addSql('DROP TABLE onboarding_software');
        $this->addSql('DROP TABLE onboarding_task');
        $this->addSql('DROP TABLE onboarding_training');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE tech_article');
        $this->addSql('DROP TABLE user_tech_state');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
