<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\AmazonPay\Controller;

use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class AmazonPay
{
    /**
     * @Query()
     * @Logged()
     */
    public function amazonPaySession(): string
    {
        $result = OxidServiceProvider::getAmazonClient()->createCheckoutSession();

        if ($result['status'] !== 201) {
            OxidServiceProvider::getLogger()->info('create checkout failed', $result);
            http_response_code(500);
        } else {
            OxidServiceProvider::getAmazonService()
                ->storeAmazonSession(PhpHelper::jsonToArray($result['response'])['checkoutSessionId']);
        }

        return serialize($result);
    }
}
