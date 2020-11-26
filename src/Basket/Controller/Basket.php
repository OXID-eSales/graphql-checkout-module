<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Controller;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Order\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Checkout\Basket\Service\Basket as BasketService;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;
use OxidEsales\GraphQL\Checkout\Payment\DataType\BasketPayment;
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
    public function basketSetPayment(ID $basketId, ID $paymentId, string $additionalInfo = ''): BasketDataType
    {
        return $this->basketService->setPayment($basketId, $paymentId, $additionalInfo);
    }

    /**
     * @Mutation()
     * @Logged()
     *
     * Additional info is some base64_encoded serialized array that will be used as dyndata.
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
     * @return BasketPayment[]
     */
    public function basketPayments(ID $basketId): array
    {
        return $this->basketService->getBasketPayments($basketId);
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function placeOrder(ID $basketId): OrderDataType
    {
        return $this->basketService->placeOrder($basketId);
    }

    /**
     * @Mutation()
     */
    public function PayPalExpress(string $productId): BasketDataType
    {
        return $this->basketService->paypalExpress($productId);
    }

    /**
     * @Mutation()
     *
     * TODO: as we are not logged in at this time, verify against PP token and payerid
     */
    public function placePayPalOrder(ID $basketId): string
    {
        return $this->basketService->paypalExpressCheckout($basketId);
    }
}
