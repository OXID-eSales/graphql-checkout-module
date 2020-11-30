<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Shared\Shop;

final class PaymentControllerHack extends PaymentControllerHack_parent
{
    public function validatePaymentForGraphql(string $paymentId)
    {
        $this->_fcpoCheckKlarnaUpdateUser($paymentId);

        /** @var mixed $return */
        $return = parent::validatePayment();

        $return = $this->_processParentReturnValue($return);

        return $this->_fcpoProcessValidation($return, $paymentId);
    }
}
