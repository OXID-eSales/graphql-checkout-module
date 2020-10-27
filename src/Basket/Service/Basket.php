<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Service;

use OxidEsales\GraphQL\Account\Address\DataType\AddressFilterList;
use OxidEsales\GraphQL\Account\Address\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Address\Exception\DeliveryAddressNotFound;
use OxidEsales\GraphQL\Account\Address\Service\DeliveryAddress as DeliveryAddressService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketAccessForbidden;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Account\Basket\Service\Basket as AccountBasketService;
use OxidEsales\GraphQL\Account\Country\Service\Country as CountryService;
use OxidEsales\GraphQL\Account\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Customer\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Account\Order\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as Repository;
use OxidEsales\GraphQL\Checkout\Basket\Exception\PlaceOrder;
use OxidEsales\GraphQL\Checkout\Basket\Infrastructure\Basket as BasketInfrastructure;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\Exception\UnavailableDeliveryMethod;
use OxidEsales\GraphQL\Checkout\Payment\Exception\PaymentValidationFailed;
use OxidEsales\GraphQL\Checkout\Payment\Exception\UnavailablePayment;
use TheCodingMachine\GraphQLite\Types\ID;

final class Basket
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Authorization */
    private $authorizationService;

    /** @var BasketInfrastructure */
    private $basketInfrastructure;

    /** @var CountryService */
    private $countryService;

    /** @var CustomerService */
    private $customerService;

    /** @var AccountBasketService */
    private $accountBasketService;

    /** @var DeliveryAddressService */
    private $deliveryAddressService;

    /** @var BasketRelationService */
    private $basketRelationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService,
        BasketInfrastructure $basketInfrastructure,
        DeliveryAddressService $deliveryAddressService,
        AccountBasketService $accountBasketService,
        CountryService $countryService,
        CustomerService $customerService,
        BasketRelationService $basketRelationService
    ) {
        $this->repository             = $repository;
        $this->authenticationService  = $authenticationService;
        $this->authorizationService   = $authorizationService;
        $this->basketInfrastructure   = $basketInfrastructure;
        $this->accountBasketService   = $accountBasketService;
        $this->countryService         = $countryService;
        $this->customerService        = $customerService;
        $this->deliveryAddressService = $deliveryAddressService;
        $this->basketRelationService  = $basketRelationService;
    }

    /**
     * @throws BasketAccessForbidden
     * @throws BasketNotFound
     * @throws DeliveryAddressNotFound
     * @throws InvalidToken
     */
    public function setDeliveryAddress(string $basketId, string $deliveryAddressId): BasketDataType
    {
        $basket = $this->getBasketById(new ID($basketId));

        if (!$this->deliveryAddressBelongsToUser($deliveryAddressId)) {
            throw DeliveryAddressNotFound::byId($deliveryAddressId);
        }

        $this->basketInfrastructure->setDeliveryAddress($basket, $deliveryAddressId);

        return $basket;
    }

    /**
     * @throws PaymentValidationFailed
     * @throws UnavailablePayment
     */
    public function setPayment(ID $basketId, ID $paymentId): BasketDataType
    {
        if (!$this->isPaymentMethodAvailableForBasket($basketId, $paymentId)) {
            throw UnavailablePayment::byId((string) $paymentId->val());
        }

        return $this->setPaymentIdBasket($basketId, $paymentId);
    }

    /**
     * @throws UnavailableDeliveryMethod
     */
    public function setDeliveryMethod(ID $basketId, ID $deliveryMethodId): BasketDataType
    {
        if (!$this->isDeliveryMethodAvailableForBasket($basketId, $deliveryMethodId)) {
            throw UnavailableDeliveryMethod::byId((string) $deliveryMethodId->val());
        }

        return $this->setDeliveryMethodIdToBasket($basketId, $deliveryMethodId);
    }

    /**
     * Check if payment method is available for user basket with concrete id
     */
    public function isPaymentMethodAvailableForBasket(ID $basketId, ID $paymentId): bool
    {
        $basket           = $this->getBasketById($basketId);
        $deliveryMethodId = $basket->getEshopModel()->getFieldData('oegql_deliverymethodid');

        if (!$deliveryMethodId) {
            throw PaymentValidationFailed::byDeliveryMethod();
        }

        $customer  = $this->customerService->customer((string) $basket->getUserId()->val());
        $countryId = $this->basketInfrastructure->getBasketDeliveryCountryId($basket);
        $country   = $this->countryService->country($countryId);

        $deliveries = $this->basketInfrastructure->getBasketAvailableDeliveryMethods(
            $customer,
            $basket,
            $country
        );

        $paymentMethods = isset($deliveries[$deliveryMethodId]) ? $deliveries[$deliveryMethodId]->getPaymentTypes() : [];

        return array_key_exists((string) $paymentId->val(), $paymentMethods);
    }

    /**
     * Updates payment id for the user basket
     */
    public function setPaymentIdBasket(ID $basketId, ID $paymentId): BasketDataType
    {
        $basket = $this->getBasketById($basketId);

        $this->basketInfrastructure->setPayment($basket, (string) $paymentId->val());

        return $basket;
    }

    /**
     * Check if delivery set is available for user basket with concrete id
     */
    public function isDeliveryMethodAvailableForBasket(ID $basketId, ID $deliveryMethodId): bool
    {
        $basket    = $this->getBasketById($basketId);
        $customer  = $this->customerService->customer((string) $basket->getUserId()->val());
        $countryId = $this->basketInfrastructure->getBasketDeliveryCountryId($basket);
        $country   = $this->countryService->country($countryId);

        $deliveries = $this->basketInfrastructure->getBasketAvailableDeliveryMethods(
            $customer,
            $basket,
            $country
        );

        return array_key_exists((string) $deliveryMethodId->val(), $deliveries);
    }

    /**
     * Update delivery set id for user basket
     * Resets payment id as it may be not available for new delivery set
     */
    public function setDeliveryMethodIdToBasket(ID $basketId, ID $deliveryId): BasketDataType
    {
        $basket = $this->getBasketById($basketId);

        $this->basketInfrastructure->setDeliveryMethod($basket, (string) $deliveryId->val());

        return $basket;
    }

    /**
     * @return DeliveryMethodDataType[]
     */
    public function getBasketDeliveryMethods(ID $basketId): array
    {
        $basket    = $this->getBasketById($basketId);
        $customer  = $this->customerService->customer((string) $basket->getUserId()->val());
        $countryId = $this->basketInfrastructure->getBasketDeliveryCountryId($basket);
        $country   = $this->countryService->country($countryId);

        return $this->basketInfrastructure->getBasketAvailableDeliveryMethods(
            $customer,
            $basket,
            $country
        );
    }

    /**
     * @return PaymentDataType[]
     */
    public function getBasketPayments(ID $basketId): array
    {
        $basket    = $this->getBasketById($basketId);
        $customer  = $this->customerService->customer((string) $basket->getUserId()->val());
        $countryId = $this->basketInfrastructure->getBasketDeliveryCountryId($basket);
        $country   = $this->countryService->country($countryId);

        $deliveries = $this->basketInfrastructure->getBasketAvailableDeliveryMethods(
            $customer,
            $basket,
            $country
        );

        $result = [];

        foreach ($deliveries as $delivery) {
            $payments = $delivery->getPaymentTypes();

            $result = array_merge($result, $payments);
        }

        return array_unique($result, SORT_REGULAR);
    }

    /**
     * @throws BasketAccessForbidden
     * @throws BasketNotFound
     * @throws InvalidToken
     */
    public function getBasketById(ID $basketId): BasketDataType
    {
        $basket = $this->accountBasketService->basket((string) $basketId->val());

        $userId = $this->authenticationService->getUserId();

        if (!$basket->belongsToUser($userId)) {
            throw BasketAccessForbidden::byAuthenticatedUser();
        }

        return $basket;
    }

    /**
     * @throws UnavailableDeliveryMethod
     * @throws UnavailablePayment
     * @throws PlaceOrder
     */
    public function placeOrder(CustomerDataType $customer, BasketDataType $userBasket): OrderDataType
    {
        $userBasket->id();
        /** @var DeliveryMethodDataType $deliveryMethod */
        $deliveryMethod = $this->basketRelationService->deliveryMethod($userBasket);
        /** @var PaymentDataType $payment */
        $payment        = $this->basketRelationService->payment($userBasket);

        if (!$this->isDeliveryMethodAvailableForBasket($userBasket->id(), $deliveryMethod->id())) {
            throw UnavailableDeliveryMethod::byId((string) $deliveryMethod->id()->val());
        }

        if (!$this->isPaymentMethodAvailableForBasket($userBasket->id(), $payment->getId())) {
            throw UnavailablePayment::byId((string) $payment->getId()->val());
        }

        return $this->basketInfrastructure->placeOrder(
            $customer,
            $userBasket
        );
    }

    private function deliveryAddressBelongsToUser(string $deliveryAddressId): bool
    {
        $belongs           = false;
        $customerAddresses = $this->deliveryAddressService->customerDeliveryAddresses(new AddressFilterList());

        /** @var DeliveryAddressDataType $address */
        foreach ($customerAddresses as $address) {
            $id      = (string) $address->id()->val();
            $belongs = ($id === $deliveryAddressId);

            if ($belongs) {
                break;
            }
        }

        return $belongs;
    }
}
