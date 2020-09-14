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
            '\\OxidEsales\\GraphQL\\Checkout\\Checkout\\Controller'    => __DIR__ . '/../../Checkout/Controller/'
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Checkout\\Checkout\\DataType'  => __DIR__ . '/../../Checkout/DataType/',
            '\\OxidEsales\\GraphQL\\Checkout\\Checkout\\Service'   => __DIR__ . '/../../Checkout/Service/',
        ];
    }
}
