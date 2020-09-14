<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\DataType;

use OxidEsales\GraphQL\Checkout\Checkout\DataType\DeliverySet as DeliverySetDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\Payment as PaymentDataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class Delivery
{
    /** @var DeliverySetDataType */
    private $deliverySetDataType;

    /** @var PaymentDataType[] */
    private $paymentDataTypes;


    public function __construct(
        DeliverySetDataType $deliverySetDataType,
        array $paymentDataTypes
    ) {
        $this->deliverySetDataType = $deliverySetDataType;
        $this->paymentDataTypes = $paymentDataTypes;
    }

    /**
     * @Field
     */
    public function deliverySet(): DeliverySetDataType
    {
        return $this->deliverySetDataType;
    }

    /**
     * @Field
     *
     * @return PaymentDataType[]
     */
    public function payments(): array
    {
        return $this->paymentDataTypes;
    }
}
