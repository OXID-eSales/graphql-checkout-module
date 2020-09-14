<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\Controller;

use OxidEsales\GraphQL\Checkout\Checkout\DataType\Delivery as DeliveryDataType;
use OxidEsales\GraphQL\Checkout\Checkout\Service\Checkout as CheckoutService;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Base\Service\Authentication;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Checkout
{
    /** @var CustomerService */
    private $customerService;

    /** @var Authentication */
    private $authenticationService;

    /** @var CheckoutService */
    private $checkoutService;

    public function __construct(
        CustomerService $customerService,
        Authentication $authenticationService,
        CheckoutService $checkoutService
    ) {
        $this->customerService       = $customerService;
        $this->authenticationService = $authenticationService;
        $this->checkoutService = $checkoutService;
    }

    /**
     * @Query()
     * @Logged()
     *
     * @return DeliveryDataType[]
     */
    public function parcelDeliveriesForBasket(string $basketId, string $countryId): array
    {
        /** @var CustomerDataType $customer */
        $customer = $this->customerService->customer(
            $this->authenticationService->getUserId()
        );

        return $this->checkoutService->parcelDeliveriesForBasket(
            $customer,
            $basketId,
            $countryId
        );
    }
}
