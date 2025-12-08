<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207154618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, customer_name VARCHAR(255) NOT NULL, customer_phone VARCHAR(20) NOT NULL, customer_address VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, total_price DOUBLE PRECISION NOT NULL, total_quantity INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, parent_order_id INT NOT NULL, pizza_id INT DEFAULT NULL, is_custom TINYINT(1) NOT NULL, quantity INT NOT NULL, final_price DOUBLE PRECISION NOT NULL, INDEX IDX_52EA1F091252C1E9 (parent_order_id), INDEX IDX_52EA1F09D41D1D42 (pizza_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_item_ingredient (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, ingredient_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_1D179326E415FB15 (order_item_id), INDEX IDX_1D179326933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F091252C1E9 FOREIGN KEY (parent_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09D41D1D42 FOREIGN KEY (pizza_id) REFERENCES pizza (id)');
        $this->addSql('ALTER TABLE order_item_ingredient ADD CONSTRAINT FK_1D179326E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id)');
        $this->addSql('ALTER TABLE order_item_ingredient ADD CONSTRAINT FK_1D179326933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F091252C1E9');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09D41D1D42');
        $this->addSql('ALTER TABLE order_item_ingredient DROP FOREIGN KEY FK_1D179326E415FB15');
        $this->addSql('ALTER TABLE order_item_ingredient DROP FOREIGN KEY FK_1D179326933FE08C');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE order_item_ingredient');
    }
}
