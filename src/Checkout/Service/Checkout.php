<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\Service;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Country\DataType as CountryDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\AvailablePayment as AvailablePaymentDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\Delivery as DeliveryDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\DeliverySet as DeliverySetDataType;
use OxidEsales\GraphQL\Account\Basket\Service\Basket as AccountBasketService;
use OxidEsales\GraphQL\Account\Country\Service\Country as CountryService;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Checkout\Checkout\Infrastructure\Checkout as CheckoutInfrastructure;

final class Checkout
{
    /** @var AccountBasketService */
    private $basketService;

    /** @var CheckoutInfrastructure */
    private $checkoutInfrastructure;

    /** @var CountryService $countryService */
    private $countryService;

    public function __construct(
        AccountBasketService $basketService,
        CheckoutInfrastructure $checkoutInfrastructure,
        CountryService $countryService
    ) {
        $this->basketService = $basketService;
        $this->checkoutInfrastructure = $checkoutInfrastructure;
        $this->countryService = $countryService;
    }

    /**
     * @return DeliveryDataType[]
     */
    public function parcelDeliveriesForBasket(
        CustomerDataType $customer,
        string $basketId,
        string $countryId,
        ?string $shippingId = null
    ): array
    {
        /** @var BasketDataType $basket */
        $basket = $this->basketService->basket($basketId);

        /** @var CountryDataType $country */
        $country = $this->countryService->country($countryId);

        return $this->checkoutInfrastructure->parcelDeliveriesForBasket($customer, $basket, $country, $shippingId);
    }

    /**
     * @return DeliverySetDataType[]
     */
    public function parcelDeliveries(CustomerDataType $customer, string $countryId): array
    {
        /** @var CountryDataType $country */
        $country = $this->countryService->country($countryId);

        return $this->checkoutInfrastructure->parcelDeliveries($customer, $country);
    }

    /**
     * @return AvailablePaymentDataType[]
     */
    public function paymentMethodsForBasket(
        CustomerDataType $customer,
        string $basketId,
        string $countryId
    ): array
    {
        /** @var BasketDataType $basket */
        $basket = $this->basketService->basket($basketId);

        /** @var CountryDataType $country */
        $country = $this->countryService->country($countryId);

        return $this->checkoutInfrastructure->paymentMethodsForBasket($customer, $basket, $country);
    }
}
