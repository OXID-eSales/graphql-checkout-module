<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\Price;

final class Payment
{
    public function getPaymentCost(
        PaymentDataType $payment,
        BasketModel $basketModel
    ): Price {
        $paymentModel = $payment->getEshopModel();

        /** @phpstan-ignore-next-line */
        $paymentModel->calculate($basketModel);

        /** @var \OxidEsales\Eshop\Core\Price $price */
        $price = $paymentModel->getPrice();

        return new Price(
            $price
        );
    }
}
