<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Shared\Service;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;

final class NamespaceMapper implements NamespaceMapperInterface
{
    public function getControllerNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Checkout\\Basket\\Controller'   => __DIR__ . '/../../Basket/Controller/',
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Checkout\\Basket\\DataType'             => __DIR__ . '/../../Basket/DataType/',
            '\\OxidEsales\\GraphQL\\Checkout\\Basket\\Service'              => __DIR__ . '/../../Basket/Service/',
            '\\OxidEsales\\GraphQL\\Checkout\\Basket\\Infrastructure'       => __DIR__ . '/../../Basket/Infrastructure/',
            '\\OxidEsales\\GraphQL\\Checkout\\DeliveryMethod\\DataType'     => __DIR__ . '/../../DeliveryMethod/DataType/',
            '\\OxidEsales\\GraphQL\\Checkout\\Payment\\DataType'            => __DIR__ . '/../../Payment/DataType/',
            '\\OxidEsales\\GraphQL\\Checkout\\DeliveryMethod\\Service'      => __DIR__ . '/../../DeliveryMethod/Service/',
        ];
    }
}
