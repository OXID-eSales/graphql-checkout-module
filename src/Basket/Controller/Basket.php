<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Controller;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Checkout\Basket\Service\Basket as BasketService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class Basket
{
    /** @var BasketService */
    private $basketService;

    public function __construct(
        BasketService $basketService
    ) {
        $this->basketService = $basketService;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function basketSetDeliveryAddress(string $basketId, string $deliveryAddressId): BasketDataType
    {
        return $this->basketService->setDeliveryAddress($basketId, $deliveryAddressId);
    }
}
