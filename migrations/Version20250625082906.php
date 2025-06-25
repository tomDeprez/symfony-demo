<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625082906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product_commande ADD commande_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_commande ADD CONSTRAINT FK_A55ACCEA82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A55ACCEA82EA2E54 ON product_commande (commande_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product_commande DROP FOREIGN KEY FK_A55ACCEA82EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_A55ACCEA82EA2E54 ON product_commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_commande DROP commande_id
        SQL);
    }
}
