<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260615201812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE onboarding_comment (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, task_id INT DEFAULT NULL, training_id INT DEFAULT NULL, software_id INT DEFAULT NULL, INDEX IDX_55BCCEFBF675F31B (author_id), INDEX IDX_55BCCEFB8DB60186 (task_id), INDEX IDX_55BCCEFBBEFD98D1 (training_id), INDEX IDX_55BCCEFBD7452741 (software_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE onboarding_comment ADD CONSTRAINT FK_55BCCEFBF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onboarding_comment ADD CONSTRAINT FK_55BCCEFB8DB60186 FOREIGN KEY (task_id) REFERENCES onboarding_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onboarding_comment ADD CONSTRAINT FK_55BCCEFBBEFD98D1 FOREIGN KEY (training_id) REFERENCES onboarding_training (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onboarding_comment ADD CONSTRAINT FK_55BCCEFBD7452741 FOREIGN KEY (software_id) REFERENCES onboarding_software (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE onboarding_comment DROP FOREIGN KEY FK_55BCCEFBF675F31B');
        $this->addSql('ALTER TABLE onboarding_comment DROP FOREIGN KEY FK_55BCCEFB8DB60186');
        $this->addSql('ALTER TABLE onboarding_comment DROP FOREIGN KEY FK_55BCCEFBBEFD98D1');
        $this->addSql('ALTER TABLE onboarding_comment DROP FOREIGN KEY FK_55BCCEFBD7452741');
        $this->addSql('DROP TABLE onboarding_comment');
    }
}
