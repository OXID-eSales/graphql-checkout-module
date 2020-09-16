<?php declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200915120929 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE `oxorder`
          ADD COLUMN `OXTESTFIELD` char(32)
          character set latin1 collate latin1_general_ci NOT NULL
          COMMENT 'Test field added as a proof of concept';");

    }

    public function down(Schema $schema) : void
    {
    }
}
