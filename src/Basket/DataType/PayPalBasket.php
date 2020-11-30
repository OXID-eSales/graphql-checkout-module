<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\DataType;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
final class PayPalBasket
{
    /** @var string */
    private $paypalCommunicationUrl;

    /** @var BasketDataType */
    private $basketDataType;

    /** @var string */
    private $paypalToken;

    public function __construct(
        BasketDataType $basketDataType,
        string $paypalCommunicationUrl,
        string $paypalToken
    ) {
        $this->paypalCommunicationUrl = $paypalCommunicationUrl;
        $this->basketDataType         = $basketDataType;
        $this->paypalToken            = $paypalToken;
    }

    /**
     * @Field
     */
    public function getPaypalCommunicationUrl(): string
    {
        return $this->paypalCommunicationUrl;
    }

    /**
     * @Field
     */
    public function getPaypalToken(): string
    {
        return $this->paypalToken;
    }

    /**
     * @Field
     */
    public function getBasket(): BasketDataType
    {
        return $this->basketDataType;
    }
}
