<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\DataType;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as AccountBasket;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * TODO: Delete this file when relation are introduced.
 * TODO: It was added for testing purposes.
 *
 * @ExtendType(class=AccountBasket::class)
 */
final class Basket
{
    /**
     * @Field()
     */
    public function deliverySetId(AccountBasket $basket): ID
    {
        $deliverySetId = (string) $basket->getEshopModel()->getFieldData('oegql_deliverysetid');

        return new ID($deliverySetId);
    }

    /**
     * @Field()
     */
    public function paymentId(AccountBasket $basket): ID
    {
        $paymentId = (string) $basket->getEshopModel()->getFieldData('oegql_paymentid');

        return new ID($paymentId);
    }
}
