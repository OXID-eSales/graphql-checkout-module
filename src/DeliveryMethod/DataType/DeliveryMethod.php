<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType;

use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 * @extendable-dataType
 */
class DeliveryMethod implements DataType
{
    /** @var EshopDeliverySetModel */
    private $deliverySetModel;

    /** @var PaymentDataType[] */
    private $paymentTypes;

    public function __construct(
        EshopDeliverySetModel $deliverySetModel,
        array $paymentTypes = []
    ) {
        $this->deliverySetModel = $deliverySetModel;
        $this->paymentTypes     = $paymentTypes;
    }

    public function getEshopModel(): EshopDeliverySetModel
    {
        return $this->deliverySetModel;
    }

    /**
     * @Field()
     */
    public function id(): ID
    {
        return new ID(
            $this->deliverySetModel->getId()
        );
    }

    /**
     * @Field()
     */
    public function title(): string
    {
        return (string) $this->deliverySetModel->getFieldData('oxtitle');
    }

    /**
     * @Field()
     *
     * @return PaymentDataType[]
     */
    public function getPaymentTypes(): array
    {
        return $this->paymentTypes;
    }

    /**
     * @Field()
     */
    public function getPosition(): int
    {
        return (int) $this->deliverySetModel->getFieldData('oxsort');
    }

    public static function getModelClass(): string
    {
        return EshopDeliverySetModel::class;
    }
}
