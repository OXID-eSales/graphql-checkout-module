<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Exception;

use OxidEsales\GraphQL\Base\Exception\NotFound;

final class PayPalExpressCheckoutException extends NotFound
{
    public static function byToken(string $token): self
    {
        return new self(sprintf('Could not place order for paypal token: %s', $token));
    }

    public static function byUser(string $token): self
    {
        return new self(sprintf('Could not find shop user for paypal token: %s', $token));
    }
}
