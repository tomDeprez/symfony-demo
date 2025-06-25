<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625073935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE statuts (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD statuts_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DE0EA5904 FOREIGN KEY (statuts_id) REFERENCES statuts (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67DE0EA5904 ON commande (statuts_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DE0EA5904
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE statuts
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67DE0EA5904 ON commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP statuts_id
        SQL);
    }
}
