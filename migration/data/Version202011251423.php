<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201006152500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `oxuserbaskets`
            ADD COLUMN `OEGQL_PAYMENTDYNDATA` text NOT NULL
                COMMENT 'Dyn data for payment';");
        $this->addSql("ALTER TABLE `oxuserbaskets`
            ADD COLUMN `OEGQL_PAYPALTOKEN` text NOT NULL
                COMMENT 'PayPal token';");
    }

    public function down(Schema $schema): void
    {
    }
}
