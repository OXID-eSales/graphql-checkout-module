<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Service;

use OxidEsales\GraphQL\Account\Address\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Address\Service\DeliveryAddress as DeliveryAddressService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=Basket::class)
 */
final class BasketRelationService
{
    /** @var DeliveryAddressService */
    private $deliveryAddressService;

    public function __construct(
        DeliveryAddressService $deliveryAddressService
    ) {
        $this->deliveryAddressService = $deliveryAddressService;
    }

    /**
     * @Field()
     */
    public function deliveryAddress(Basket $basket): DeliveryAddress
    {
        $addressId = (string) $basket->getEshopModel()->getFieldData('oegql_deladdressid');

        return $this->deliveryAddressService->getDeliveryAddress($addressId);
    }
}
