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
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as Repository;
use OxidEsales\GraphQL\Checkout\Basket\Infrastructure\Basket as BasketInfrastructure;

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

    /** @var DeliveryAddressService */
    private $deliveryAddressService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService,
        BasketInfrastructure $basketInfrastructure,
        DeliveryAddressService $deliveryAddressService
    ) {
        $this->repository             = $repository;
        $this->authenticationService  = $authenticationService;
        $this->authorizationService   = $authorizationService;
        $this->basketInfrastructure   = $basketInfrastructure;
        $this->deliveryAddressService = $deliveryAddressService;
    }

    /**
     * @throws BasketAccessForbidden
     * @throws BasketNotFound
     * @throws DeliveryAddressNotFound
     */
    public function setDeliveryAddress(string $basketId, string $deliveryAddressId): BasketDataType
    {
        try {
            /** @var BasketDataType $basket */
            $basket = $this->repository->getById(
                $basketId,
                BasketDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw BasketNotFound::byId($basketId);
        }

        $userId = $this->authenticationService->getUserId();

        if (!$basket->belongsToUser($userId)) {
            throw BasketAccessForbidden::byAuthenticatedUser();
        }

        if (!$this->deliveryAddressBelongsToUser($deliveryAddressId)) {
            throw DeliveryAddressNotFound::byId($deliveryAddressId);
        }

        $this->basketInfrastructure->setDeliveryAddress($basket, $deliveryAddressId);

        return $basket;
    }

    /**
     * @throws DeliveryAddressNotFound
     * @throws InvalidLogin
     */
    public function getDeliveryAddress(string $id): DeliveryAddressDataType
    {
        /** Only logged in users can query delivery addresses */
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        try {
            /** @var DeliveryAddressDataType $deliveryAddress */
            $deliveryAddress = $this->repository->getById(
                $id,
                DeliveryAddressDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw DeliveryAddressNotFound::byId($id);
        }

        return $deliveryAddress;
    }

    private function deliveryAddressBelongsToUser(string $deliveryAddressId): bool
    {
        $belongs          = false;
        $customerAdresses = $this->deliveryAddressService->customerDeliveryAddresses(new AddressFilterList());

        /** @var DeliveryAddressDataType $address */
        foreach ($customerAdresses as $address) {
            $id      = (string) $address->id()->val();
            $belongs = ($id === $deliveryAddressId);

            if ($belongs) {
                break;
            }
        }

        return $belongs;
    }
}
