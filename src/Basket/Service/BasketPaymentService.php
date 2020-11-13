<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Service;

use OxidEsales\GraphQL\Catalogue\Shared\DataType\Price;
use OxidEsales\GraphQL\Checkout\Basket\Infrastructure\Payment as PaymentInfrastructure;
use OxidEsales\GraphQL\Checkout\Payment\DataType\BasketPayment;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=BasketPayment::class)
 */
final class BasketPaymentService
{
    /** @var PaymentInfrastructure */
    private $paymentInfrastructure;

    public function __construct(
        PaymentInfrastructure $paymentInfrastructure
    ) {
        $this->paymentInfrastructure = $paymentInfrastructure;
    }

    /**
     * @Field()
     */
    public function cost(BasketPayment $payment): Price
    {
        return $this->paymentInfrastructure->getPaymentCost(
            $payment,
            $payment->getBasketModel()
        );
    }
}
