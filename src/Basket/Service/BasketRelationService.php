<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Service;

use OxidEsales\GraphQL\Account\Address\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment;
use OxidEsales\GraphQL\Checkout\Basket\Service\Basket as BasketService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=Basket::class)
 */
final class BasketRelationService
{
    /** @var BasketService */
    private $basketService;

    public function __construct(
        BasketService $basketService
    ) {
        $this->basketService = $basketService;
    }

    /**
     * @Field()
     */
    public function deliveryAddress(Basket $basket): ?DeliveryAddress
    {
        $addressId = (string) $basket->getEshopModel()->getFieldData('oegql_deladdressid');

        return $this->basketService->getDeliveryAddress($addressId);
    }

    /**
     * @Field()
     */
    public function payment(Basket $basket): ?Payment
    {
        $paymentId = (string) $basket->getEshopModel()->getFieldData('oegql_paymentid');

        return $this->basketService->getPayment($paymentId);
    }
}
