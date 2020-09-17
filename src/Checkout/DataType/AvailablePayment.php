<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\DataType;

use OxidEsales\GraphQL\Checkout\Checkout\DataType\DeliverySet as DeliverySetDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\CheckoutPayment as PaymentDataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class AvailablePayment
{
    /** @var DeliverySetDataType[] */
    private $deliverySetDataTypes;

    /** @var PaymentDataType[] */
    private $paymentDataType;

    public function __construct(
        PaymentDataType $paymentDataType,
        array $deliverySetDataTypes
    ) {
        $this->paymentDataType = $paymentDataType;
        $this->deliverySetDataTypes = $deliverySetDataTypes;
    }

    /**
     * @Field
     */
    public function payment(): PaymentDataType
    {
        return $this->paymentDataType;
    }

    /**
     * @Field
     *
     * @return DeliverySetDataType[]
     */
    public function deliverySets(): array
    {
        return $this->deliverySetDataTypes;
    }
}
