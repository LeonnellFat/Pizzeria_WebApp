<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209164107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredient_stock (id INT AUTO_INCREMENT NOT NULL, ingredient_id INT NOT NULL, quantity INT NOT NULL, unit VARCHAR(255) DEFAULT NULL, reorder_level INT DEFAULT NULL, last_restocked DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_520431A1933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pizza_stock (id INT AUTO_INCREMENT NOT NULL, pizza_id INT NOT NULL, quantity INT NOT NULL, reorder_level INT DEFAULT NULL, last_restocked DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BD4754B8D41D1D42 (pizza_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ingredient_stock ADD CONSTRAINT FK_520431A1933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE pizza_stock ADD CONSTRAINT FK_BD4754B8D41D1D42 FOREIGN KEY (pizza_id) REFERENCES pizza (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient_stock DROP FOREIGN KEY FK_520431A1933FE08C');
        $this->addSql('ALTER TABLE pizza_stock DROP FOREIGN KEY FK_BD4754B8D41D1D42');
        $this->addSql('DROP TABLE ingredient_stock');
        $this->addSql('DROP TABLE pizza_stock');
    }
}
