<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Controller;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Order\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Checkout\Basket\Service\Basket as BasketService;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Types\ID;

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

    /**
     * @Mutation()
     * @Logged()
     */
    public function basketSetPayment(ID $basketId, ID $paymentId): BasketDataType
    {
        return $this->basketService->setPayment($basketId, $paymentId);
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function basketSetDeliveryMethod(ID $basketId, ID $deliveryMethodId): BasketDataType
    {
        return $this->basketService->setDeliveryMethod($basketId, $deliveryMethodId);
    }

    /**
     * @Query
     * @Logged()
     *
     * @return DeliveryMethodDataType[]
     */
    public function basketDeliveryMethods(ID $basketId): array
    {
        return $this->basketService->getBasketDeliveryMethods($basketId);
    }

    /**
     * Returns all payments that can be used for particular basket.
     *
     * @Query
     * @Logged()
     *
     * @return PaymentDataType[]
     */
    public function basketPayments(ID $basketId): array
    {
        return $this->basketService->getBasketPayments($basketId);
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function placeOrder(ID $basketId, string $paypalToken, string $paypalPayerId): OrderDataType
    {
        return $this->basketService->placeOrder($basketId, $paypalToken, $paypalPayerId);
    }
}
