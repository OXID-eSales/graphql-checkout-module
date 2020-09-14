<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\DataType;

use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class DeliverySet implements DataType
{
    /** @var EshopDeliverySetModel */
    private $deliverySet;

    public function __construct(
        EshopDeliverySetModel $deliverySet
    ) {
        $this->deliverySet = $deliverySet;
    }

    public function getEshopModel(): EshopDeliverySetModel
    {
        return $this->deliverySet;
    }

    /**
     * @Field()
     */
    public function id(): ID
    {
        return new ID(
            $this->deliverySet->getId()
        );
    }

    /**
     * @Field()
     */
    public function title(): string
    {
        return (string) $this->deliverySet->getFieldData('oxtitle');
    }

    /**
     * @return class-string
     */
    public static function getModelClass(): string
    {
        return EshopDeliverySetModel::class;
    }
}
