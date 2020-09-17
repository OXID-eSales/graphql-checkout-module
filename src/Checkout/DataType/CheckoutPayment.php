<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\DataType;

use OxidEsales\Eshop\Application\Model\Payment as EshopPaymentModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class CheckoutPayment implements DataType
{
    /** @var EshopPaymentModel */
    private $payment;

    public function __construct(
        EshopPaymentModel $payment
    ) {
        $this->payment = $payment;
    }

    public function getEshopModel(): EshopPaymentModel
    {
        return $this->payment;
    }

    /**
     * @Field()
     */
    public function id(): ID
    {
        return new ID(
            $this->payment->getId()
        );
    }

    /**
     * @Field()
     */
    public function description(): string
    {
        return (string) $this->payment->getFieldData('oxdesc');
    }

    /**
     * @return class-string
     */
    public static function getModelClass(): string
    {
        return EshopPaymentModel::class;
    }
}
