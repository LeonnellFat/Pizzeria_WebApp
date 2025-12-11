<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210213905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient_stock DROP FOREIGN KEY FK_520431A1933FE08C');
        $this->addSql('ALTER TABLE ingredient_stock ADD CONSTRAINT FK_520431A1933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item_ingredient DROP FOREIGN KEY FK_1D179326933FE08C');
        $this->addSql('ALTER TABLE order_item_ingredient ADD CONSTRAINT FK_1D179326933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pizza_stock DROP FOREIGN KEY FK_BD4754B8D41D1D42');
        $this->addSql('ALTER TABLE pizza_stock ADD CONSTRAINT FK_BD4754B8D41D1D42 FOREIGN KEY (pizza_id) REFERENCES pizza (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pizza_stock DROP FOREIGN KEY FK_BD4754B8D41D1D42');
        $this->addSql('ALTER TABLE pizza_stock ADD CONSTRAINT FK_BD4754B8D41D1D42 FOREIGN KEY (pizza_id) REFERENCES pizza (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ingredient_stock DROP FOREIGN KEY FK_520431A1933FE08C');
        $this->addSql('ALTER TABLE ingredient_stock ADD CONSTRAINT FK_520431A1933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_item_ingredient DROP FOREIGN KEY FK_1D179326933FE08C');
        $this->addSql('ALTER TABLE order_item_ingredient ADD CONSTRAINT FK_1D179326933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
